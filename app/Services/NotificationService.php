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
    /**
     * Terapkan SMTP config runtime dari database.
     * Dipanggil sebelum setiap operasi kirim email.
     */
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
     * Kirim email ke approver yang tepat setelah submission dibuat/disubmit.
     *
     * Logika:
     * - Jika ada terusan → kirim ke departemen urutan pertama (terusan pertama)
     * - Jika tidak ada terusan → kirim langsung ke id_kepada (final approver)
     */
    public function notifyOnSubmit(PengajuanSurat $submission): void
    {
        if (!$this->applySmtp()) return;

        $submission->load(['terusans.departemen', 'kepada', 'user', 'perusahaan', 'jenisDokumen']);

        $firstTerusan = $submission->terusans()
            ->where('status', 'waiting')
            ->orderBy('urutan')
            ->with('departemen.users')
            ->first();

        if ($firstTerusan) {
            // Ada terusan → kirim ke semua user di departemen terusan pertama
            $this->sendToTerusan($submission, $firstTerusan);
        } else {
            // Tidak ada terusan → kirim langsung ke final approver
            $this->sendToFinalApprover($submission);
        }
    }

    /**
     * Dipanggil setelah terusan di-approve.
     * Cek apakah masih ada terusan berikutnya, atau langsung final.
     */
    public function notifyOnTerusanApproved(PengajuanSurat $submission, PengajuanTerusan $approvedTerusan): void
    {
        if (!$this->applySmtp()) return;

        $submission->load(['terusans.departemen', 'kepada', 'user', 'perusahaan', 'jenisDokumen']);

        // Cari terusan berikutnya yang masih waiting
        $nextTerusan = $submission->terusans()
            ->where('status', 'waiting')
            ->where('urutan', '>', $approvedTerusan->urutan)
            ->orderBy('urutan')
            ->with('departemen.users')
            ->first();

        if ($nextTerusan) {
            // Masih ada terusan berikutnya
            $this->sendToTerusan($submission, $nextTerusan);
        } else {
            // Semua terusan sudah approve → kirim ke final approver
            $this->sendToFinalApprover($submission);
        }
    }

    /**
     * Dipanggil setelah final approval (kepada) di-approve.
     * Kirim notifikasi ke pengaju bahwa suratnya disetujui.
     */
    public function notifyOnFinalApproved(PengajuanSurat $submission): void
    {
        if (!$this->applySmtp()) return;

        $submission->load(['user', 'perusahaan', 'jenisDokumen', 'kepada']);

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Submitter has no email.', [
                'pengajuan_id' => $submission->id,
            ]);
            return;
        }

        try {
            Mail::to($submitter->email)->send(new SubmissionApproved($submission));

            Log::info('NotificationService: Approved email sent to submitter.', [
                'pengajuan_id' => $submission->id,
                'email'        => $submitter->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService: Failed to send approved email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dipanggil ketika submission di-reject (terusan atau final).
     * Kirim notifikasi ke pengaju bahwa suratnya ditolak.
     */
    public function notifyOnRejected(PengajuanSurat $submission, string $catatan, string $rejectedBy): void
    {
        if (!$this->applySmtp()) return;

        $submission->load(['user', 'perusahaan', 'jenisDokumen', 'kepada']);

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Submitter has no email for rejection.', [
                'pengajuan_id' => $submission->id,
            ]);
            return;
        }

        try {
            Mail::to($submitter->email)->send(
                new SubmissionRejected($submission, $catatan, $rejectedBy)
            );

            Log::info('NotificationService: Rejected email sent to submitter.', [
                'pengajuan_id' => $submission->id,
                'email'        => $submitter->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService: Failed to send rejection email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Kirim email permintaan forwarding approval ke semua user
     * di departemen terusan yang bersangkutan.
     */
    private function sendToTerusan(PengajuanSurat $submission, PengajuanTerusan $terusan): void
    {
        // Ambil semua user di departemen tsb yang punya email
        $users = \App\Models\User::where('id_departemen', $terusan->id_departemen)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            Log::warning('NotificationService: No users with email in department.', [
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

                Log::info('NotificationService: Forwarding request sent.', [
                    'pengajuan_id'  => $submission->id,
                    'urutan'        => $terusan->urutan,
                    'email'         => $user->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('NotificationService: Failed to send forwarding email.', [
                    'pengajuan_id' => $submission->id,
                    'email'        => $user->email,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Kirim email permintaan final approval ke id_kepada.
     */
    private function sendToFinalApprover(PengajuanSurat $submission): void
    {
        $kepada = $submission->kepada;

        if (!$kepada || !$kepada->email) {
            Log::warning('NotificationService: Final approver has no email.', [
                'pengajuan_id' => $submission->id,
                'id_kepada'    => $submission->id_kepada,
            ]);
            return;
        }

        try {
            Mail::to($kepada->email)->send(new FinalApprovalRequest($submission, $kepada));

            Log::info('NotificationService: Final approval request sent.', [
                'pengajuan_id' => $submission->id,
                'email'        => $kepada->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService: Failed to send final approval email.', [
                'pengajuan_id' => $submission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}