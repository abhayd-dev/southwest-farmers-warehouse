<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OverReceiptApprovalRequest extends Mailable
{
    public function __construct(
        public PurchaseOrder $po,
        public string $approveUrl,
        public string $rejectUrl,
    ) {
        $this->po->loadMissing('vendor');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Approval needed: more received than ordered on PO #{$this->po->po_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.over-receipt-approval');
    }
}
