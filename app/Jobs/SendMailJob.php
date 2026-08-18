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
        // Apply SMTP dari DB sebelum kirim
        $smtp = SmtpSetting::active();
        if (!$smtp) {
            Log::warning('SendMailJob: No active SMTP setting.');
            return;
        }
        $smtp->applyToMailer();

        try {
            Mail::to($this->to)->send($this->mailable);
            Log::info('SendMailJob: Email terkirim ke ' . $this->to);
        } catch (\Throwable $e) {
            Log::error('SendMailJob: Gagal kirim email.', [
                'to'    => $this->to,
                'error' => $e->getMessage(),
            ]);
            throw $e; // lempar ulang agar queue retry
        }
    }
}