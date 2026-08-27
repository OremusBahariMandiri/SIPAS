<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Jobs\SendMailJob;
use App\Mail\ForwardingApprovalRequest;
use App\Mail\FinalApprovalRequest;
use App\Mail\SubmissionApproved;
use App\Mail\SubmissionRejected;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyOnSubmit(PengajuanSurat $submission): void
    {
        $submission = PengajuanSurat::with([
            'terusans',
            'kepada',
            'user',
            'perusahaan',
            'jenisDokumen',
        ])->find($submission->id);

        if (!$submission) return;

        Log::info('NotificationService: notifyOnSubmit', [
            'pengajuan_id'   => $submission->id,
            'jumlah_terusan' => $submission->terusans->count(),
            'id_kepada'      => $submission->id_kepada,
        ]);

        $firstTerusan = $submission->terusans
            ->where('status', 'waiting')
            ->sortBy('urutan')
            ->first();

        if ($firstTerusan) {
            Log::info('NotificationService: Ada terusan urutan ' . $firstTerusan->urutan);
            $this->sendToTerusan($submission, $firstTerusan);
        } else {
            Log::info('NotificationService: Tidak ada terusan — langsung ke final approver');
            $this->sendToFinalApprover($submission);
        }
    }

    public function notifyOnTerusanApproved(PengajuanSurat $submission, int $approvedUrutan): void
    {
        $submission = PengajuanSurat::with([
            'terusans',
            'kepada',
            'user',
            'perusahaan',
            'jenisDokumen',
        ])->find($submission->id);

        if (!$submission) return;

        Log::info('NotificationService: notifyOnTerusanApproved', [
            'pengajuan_id'    => $submission->id,
            'approved_urutan' => $approvedUrutan,
            'status_terusan'  => $submission->terusans->pluck('status', 'urutan')->toArray(),
        ]);

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

    public function notifyOnFinalApproved(PengajuanSurat $submission): void
    {
        $submission = PengajuanSurat::with(['user', 'perusahaan', 'jenisDokumen', 'kepada'])
            ->find($submission->id);

        if (!$submission) return;

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Pengaju tidak punya email.', [
                'pengajuan_id' => $submission->id,
            ]);
            return;
        }

        Log::info('NotificationService: Queue approved ke ' . $submitter->email);

        // Langsung dispatch, tidak perlu applySmtp() — sudah ditangani di dalam Job
        SendMailJob::dispatch($submitter->email, new SubmissionApproved($submission));
    }

    public function notifyOnRejected(PengajuanSurat $submission, string $catatan, string $rejectedBy): void
    {
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

        Log::info('NotificationService: Queue rejected ke ' . $submitter->email);

        SendMailJob::dispatch($submitter->email, new SubmissionRejected($submission, $catatan, $rejectedBy));
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function sendToTerusan(PengajuanSurat $submission, PengajuanTerusan $terusan): void
    {
        // Kirim ke user CC yang spesifik, bukan seluruh departemen
        $user = \App\Models\User::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('id', $terusan->id_user)
            ->first();

        if (!$user) {
            Log::warning('NotificationService: CC user tidak punya email.', [
                'pengajuan_id' => $submission->id,
                'id_user'      => $terusan->id_user,
            ]);
            return;
        }

        Log::info('NotificationService: Queue terusan ke ' . $user->email);
        SendMailJob::dispatch($user->email, new ForwardingApprovalRequest($submission, $terusan, $user));
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

        Log::info('NotificationService: Queue final approval ke ' . $kepada->email);

        SendMailJob::dispatch($kepada->email, new FinalApprovalRequest($submission, $kepada));
    }

    /**
     * Dipanggil saat CC approve atau reject — kirim info ke pengaju.
     */
    public function notifySubmitterOnTerusanAction(
        PengajuanSurat $submission,
        string $aksi,           // 'approve' | 'reject'
        string $actorName,
        ?string $catatan = null
    ): void {
        if (!$this->applySmtp()) return;

        $submission = PengajuanSurat::with(['user', 'perusahaan', 'jenisDokumen'])
            ->find($submission->id);

        if (!$submission) return;

        $submitter = $submission->user;
        if (!$submitter || !$submitter->email) {
            Log::warning('NotificationService: Pengaju tidak punya email untuk notif terusan action.', [
                'pengajuan_id' => $submission->id,
            ]);
            return;
        }

        Log::info("NotificationService: Queue terusan-{$aksi} info ke pengaju " . $submitter->email);

        if ($aksi === 'reject') {
            SendMailJob::dispatch(
                $submitter->email,
                new SubmissionRejected($submission, $catatan ?? '-', $actorName)
            );
        } else {
            // Gunakan mailable baru khusus info progress CC
            SendMailJob::dispatch(
                $submitter->email,
                new \App\Mail\TerusanApprovedInfo($submission, $actorName)
            );
        }
    }
}
