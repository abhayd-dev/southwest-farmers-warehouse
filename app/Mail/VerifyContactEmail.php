<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyContactEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $label       What this email address is used for, e.g. "Purchase Order #PO-1004 approvals"
     * @param string $recipient   The email address being verified (shown in the body for the recipient's own confirmation)
     * @param string $verifyUrl   Signed, time-limited link that marks the address verified
     */
    public function __construct(
        public string $label,
        public string $recipient,
        public string $verifyUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Please confirm your email address',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-contact-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
