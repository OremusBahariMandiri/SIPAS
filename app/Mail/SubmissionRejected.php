<?php

namespace App\Mail;

use App\Models\Data\PengajuanSurat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PengajuanSurat $submission,
        public readonly string         $catatan,
        public readonly string         $rejectedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Rejected] ' . $this->submission->nomor_surat
                   . ' — ' . $this->submission->perihal,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission-rejected',
        );
    }
}