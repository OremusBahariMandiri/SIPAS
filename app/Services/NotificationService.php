<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\Settings\SmtpSetting;
use App\Mail\ForwardingApprovalRequest;
use App\Mail\FinalApprovalRequest;
use App\Mail\SubmissionApproved;
use App\Mail\SubmissionRejected;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private function applySmtp(): bool
    {
        $smtp = SmtpSetting::active();
        if (!$smtp) {
            Log::warning('NotificationService: No active SMTP setting found.');
            return false;
        }
        $smtp->applyToMailer();
        return true;
    }

    /**
     * Dipanggil saat submission di-submit.
     *
     * FIX 1: Fresh load dari DB agar terusan yang baru disimpan ikut terbaca.
     * FIX 2: Gunakan koleksi (bukan query builder) supaya tidak kena cache relasi.
     */
    public function notifyOnSubmit(PengajuanSurat $submission): void
    {
        if (!$this->applySmtp()) return;

        // Fresh load — wajib agar terusan yang baru disimpan ikut terbaca
        $submission = PengajuanSurat::with([
            'terusans', 'kepada', 'user', 'perusahaan', 'jenisDokumen',
        ])->find($submission->id);

        if (!$submission) return;

        Log::info('NotificationService: notifyOnSubmit', [
            'pengajuan_id'   => $submission->id,
            'jumlah_terusan' => $submission->terusans->count(),
            'id_kepada'      => $submission->id_kepada,
        ]);

        // Ambil dari koleksi — bukan query builder — supaya tidak terpengaruh cache
        $firstTerusan = $submission->terusans
            ->where('status', 'waiting')
            ->sortBy('urutan')
            ->first();

        if ($firstTerusan) {
            Log::info('NotificationService: Ada terusan urutan ' . $firstTerusan->urutan . ' — kirim ke departemen');
            $this->sendToTerusan($submission, $firstTerusan);
        } else {
            Log::info('NotificationService: Tidak ada terusan — langsung ke final approver');
            $this->sendToFinalApprover($submission);
        }
    }

    /**
     * Dipanggil SETELAH terusan diupdate ke 'approved' di DB.
     *
     * FIX: Signature diubah — terima $approvedUrutan (int) bukan object terusan,
     *      agar tidak pakai data stale dari sebelum update.
     * FIX: Fresh load submission supaya status terusan sudah terupdate.
     */
    public function notifyOnTerusanApproved(PengajuanSurat $submission, int $approvedUrutan): void
    {
        if (!$this->applySmtp()) return;

        // Fresh load setelah DB sudah diupdate
        $submission = PengajuanSurat::with([
            'terusans', 'kepada', 'user', 'perusahaan', 'jenisDokumen',
        ])->find($submission->id);

        if (!$submission) return;

        Log::info('NotificationService: notifyOnTerusanApproved', [
            'pengajuan_id'    => $submission->id,
            'approved_urutan' => $approvedUrutan,
            'status_terusan'  => $submission->terusans->pluck('status', 'urutan')->toArray(),
        ]);

        // Cari terusan berikutnya yang masih waiting dengan urutan lebih besar
        $nextTerusan = $submission->terusans
            ->where('status', 'waiting')
            ->where('urutan', '>', $approvedUrutan)
            ->sortBy('urutan')
            ->first();

        if ($nextTerusan) {
            Log::info('NotificationService: Ada terusan berikutnya urutan ' . $nextTerusan->urutan);
            $this->sendToTerusan($submission, $nextTerusan);
        } else {
            Log::info('NotificationService: Semua terusan selesai — kirim ke final approver');
            $this->sendToFinalApprover($submission);
        }
    }

    /**
     * Dipanggil setelah final approval di-approve.
     * Kirim notifikasi ke pengaju bahwa surat disetujui.
     */
    public function notifyOnFinalApproved(PengajuanSurat $submission): void
    {
        if (!$this->applySmtp()) return;

        $submission = PengajuanSurat::with(['user', 'perusahaan', 'jenisDokumen', 'kepada'])
            ->find($submission->id);

        if (!$submission) return;

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Pengaju tidak punya email.', [
                'pengajuan_id' => $submission->id,
                'id_user'      => $submission->id_user,
            ]);
            return;
        }

        Log::info('NotificationService: Kirim approved ke pengaju ' . $submitter->email);

        try {
            Mail::to($submitter->email)->send(new SubmissionApproved($submission));
        } catch (\Throwable $e) {
            Log::error('NotificationService: Gagal kirim approved email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dipanggil saat submission di-reject (terusan atau final).
     * Kirim notifikasi ke pengaju.
     */
    public function notifyOnRejected(PengajuanSurat $submission, string $catatan, string $rejectedBy): void
    {
        if (!$this->applySmtp()) return;

        $submission = PengajuanSurat::with(['user', 'perusahaan', 'jenisDokumen', 'kepada'])
            ->find($submission->id);

        if (!$submission) return;

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Pengaju tidak punya email untuk rejection.', [
                'pengajuan_id' => $submission->id,
            ]);
            return;
        }

        Log::info('NotificationService: Kirim rejected ke pengaju ' . $submitter->email);

        try {
            Mail::to($submitter->email)->send(
                new SubmissionRejected($submission, $catatan, $rejectedBy)
            );
        } catch (\Throwable $e) {
            Log::error('NotificationService: Gagal kirim rejection email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Kirim ke semua user di departemen terusan.
     *
     * FIX: Exclude final approver (id_kepada) dan pengaju (id_user)
     *      agar mereka tidak menerima email terusan.
     */
    private function sendToTerusan(PengajuanSurat $submission, PengajuanTerusan $terusan): void
    {
        $users = \App\Models\User::where('id_departemen', $terusan->id_departemen)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('id', '!=', $submission->id_kepada)  // exclude final approver
            ->where('id', '!=', $submission->id_user)    // exclude pengaju
            ->get();

        Log::info('NotificationService: sendToTerusan', [
            'pengajuan_id'  => $submission->id,
            'id_departemen' => $terusan->id_departemen,
            'urutan'        => $terusan->urutan,
            'jumlah_user'   => $users->count(),
            'emails'        => $users->pluck('email')->toArray(),
        ]);

        if ($users->isEmpty()) {
            Log::warning('NotificationService: Tidak ada user dengan email di departemen ini.', [
                'id_departemen' => $terusan->id_departemen,
                'pengajuan_id'  => $submission->id,
            ]);
            return;
        }

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(
                    new ForwardingApprovalRequest($submission, $terusan, $user)
                );
                Log::info('NotificationService: Email terusan terkirim ke ' . $user->email);
            } catch (\Throwable $e) {
                Log::error('NotificationService: Gagal kirim email terusan.', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendToFinalApprover(PengajuanSurat $submission): void
    {
        $kepada = $submission->kepada ?? \App\Models\User::find($submission->id_kepada);

        if (!$kepada || !$kepada->email) {
            Log::warning('NotificationService: Final approver tidak punya email.', [
                'pengajuan_id' => $submission->id,
                'id_kepada'    => $submission->id_kepada,
            ]);
            return;
        }

        Log::info('NotificationService: Kirim final approval ke ' . $kepada->email);

        try {
            Mail::to($kepada->email)->send(new FinalApprovalRequest($submission, $kepada));
        } catch (\Throwable $e) {
            Log::error('NotificationService: Gagal kirim final approval email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}