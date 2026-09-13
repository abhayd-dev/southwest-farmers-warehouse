<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ShortageReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $po,
        public Collection $shortageItems,
    ) {
        $this->po->loadMissing('vendor');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Shortage on PO #{$this->po->po_number} — {$this->po->vendor?->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shortage-report',
            with: [
                'po' => $this->po,
                'shortageItems' => $this->shortageItems,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('warehouse.purchase-orders.shortage-pdf', [
            'po' => $this->po,
            'shortageItems' => $this->shortageItems,
        ])->setPaper('a4', 'portrait');

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                "Shortage-{$this->po->po_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
