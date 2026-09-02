<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\Settings\SmtpSetting;
use App\Jobs\SendMailJob;
use App\Mail\ForwardingApprovalRequest;
use App\Mail\FinalApprovalRequest;
use App\Mail\SubmissionApproved;
use App\Mail\SubmissionRejected;
use App\Mail\TerusanApprovedInfo;
use App\Mail\MonitoringPassedInfo;
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

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Dipanggil saat submission di-submit.
     */
    public function notifyOnSubmit(PengajuanSurat $submission): void
    {
        if (!$this->applySmtp()) return;

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

    /**
     * Dipanggil SETELAH terusan diupdate ke 'approved' di DB.
     */
    public function notifyOnTerusanApproved(PengajuanSurat $submission, int $approvedUrutan): void
    {
        if (!$this->applySmtp()) return;

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

    /**
     * Dipanggil saat CC approve — kirim info progress ke pengaju.
     */
    public function notifySubmitterOnTerusanAction(
        PengajuanSurat $submission,
        string $aksi,
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
            SendMailJob::dispatch(
                $submitter->email,
                new TerusanApprovedInfo($submission, $actorName)
            );
        }
    }

    /**
     * Dipanggil setelah final approval di-approve.
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

        Log::info('NotificationService: Queue approved ke ' . $submitter->email);

        SendMailJob::dispatch($submitter->email, new SubmissionApproved($submission));
    }

    /**
     * Dipanggil saat submission di-reject (terusan atau final).
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

        Log::info('NotificationService: Queue rejected ke ' . $submitter->email);

        SendMailJob::dispatch(
            $submitter->email,
            new SubmissionRejected($submission, $catatan, $rejectedBy)
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kirim ke user CC yang spesifik (by id_user, bukan by departemen).
     */
    private function sendToTerusan(PengajuanSurat $submission, PengajuanTerusan $terusan): void
    {
        $user = \App\Models\User::where('id', $terusan->id_user)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->first();

        if (!$user) {
            Log::warning('NotificationService: CC user tidak punya email.', [
                'pengajuan_id' => $submission->id,
                'id_user'      => $terusan->id_user,
                'urutan'       => $terusan->urutan,
            ]);
            return;
        }

        Log::info('NotificationService: Queue terusan ke ' . $user->email, [
            'pengajuan_id' => $submission->id,
            'urutan'       => $terusan->urutan,
        ]);

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

    public function notifyMonitoringPassed(PengajuanSurat $submission, PengajuanTerusan $terusan): void
    {
        if (!$this->applySmtp()) return;

        $user = \App\Models\User::where('id', $terusan->id_user)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->first();

        if (!$user) {
            Log::warning('NotificationService: Monitoring user tidak punya email.', [
                'pengajuan_id' => $submission->id,
                'id_user'      => $terusan->id_user,
                'urutan'       => $terusan->urutan,
            ]);
            return;
        }

        Log::info('NotificationService: Queue monitoring-passed ke ' . $user->email, [
            'pengajuan_id' => $submission->id,
            'urutan'       => $terusan->urutan,
        ]);

        SendMailJob::dispatch(
            $user->email,
            new MonitoringPassedInfo($submission, $terusan, $user),
        );
    }
}
