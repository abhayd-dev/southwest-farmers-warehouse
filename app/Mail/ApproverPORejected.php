<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApproverPORejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $po,
    ) {
        $this->po->loadMissing('vendor');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You rejected PO #{$this->po->po_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approver-po-rejected',
            with: ['po' => $this->po],
        );
    }
}
