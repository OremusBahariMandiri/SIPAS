<?php

namespace App\Mail;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonitoringPassedInfo extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PengajuanSurat   $submission,
        public readonly PengajuanTerusan $terusan,
        public readonly User             $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Monitoring] ' . $this->submission->nomor_surat
                   . ' — ' . $this->submission->perihal,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monitoring-passed-info',
        );
    }
}