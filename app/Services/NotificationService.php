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
            'terusans', 'kepada', 'user', 'perusahaan', 'jenisDokumen',
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
            'terusans', 'kepada', 'user', 'perusahaan', 'jenisDokumen',
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
        $users = \App\Models\User::where('id_departemen', $terusan->id_departemen)
            ->whereNotNull('email')
            ->where('email', '!=', '')
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
            SendMailJob::dispatch($user->email, new ForwardingApprovalRequest($submission, $terusan, $user));
            Log::info('NotificationService: Queue terusan ke ' . $user->email);
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

        Log::info('NotificationService: Queue final approval ke ' . $kepada->email);

        SendMailJob::dispatch($kepada->email, new FinalApprovalRequest($submission, $kepada));
    }
}