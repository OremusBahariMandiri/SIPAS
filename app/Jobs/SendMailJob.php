<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Settings\SmtpSetting;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;           // retry 3x jika gagal
    public int $timeout = 60;          // timeout 60 detik per percobaan

    public function __construct(
        private readonly string   $to,
        private readonly Mailable $mailable,
    ) {}

    public function handle(): void
    {
        $startTime = microtime(true);

        $smtp = SmtpSetting::active();
        if (!$smtp) {
            Log::warning('SendMailJob: No active SMTP setting.');
            return;
        }
        $smtp->applyToMailer();

        try {
            Mail::to($this->to)->send($this->mailable);
            Log::info('SendMailJob: Email terkirim ke ' . $this->to);

            // ── Catat ke completed_jobs ──────────────────────────────────────
            $runTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            \App\Models\CompletedJob::create([
                'uuid'         => $this->job?->uuid() ?? \Illuminate\Support\Str::uuid(),
                'queue'        => $this->job?->getQueue() ?? 'default',
                'display_name' => static::class,
                'payload'      => ['to' => $this->to, 'mailable' => class_basename($this->mailable)],
                'attempts'     => $this->attempts(),
                'run_time_ms'  => $runTimeMs,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SendMailJob: Gagal kirim email.', [
                'to'    => $this->to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
