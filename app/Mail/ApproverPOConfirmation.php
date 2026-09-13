<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApproverPOConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $po,
        public string $cancelUrl,
    ) {
        $this->po->loadMissing('vendor');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You approved PO #{$this->po->po_number} — sent to {$this->po->vendor?->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approver-po-confirmation',
            with: ['po' => $this->po, 'cancelUrl' => $this->cancelUrl],
        );
    }
}
