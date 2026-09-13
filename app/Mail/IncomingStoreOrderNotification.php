<?php

namespace App\Mail;

use App\Models\StorePurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncomingStoreOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StorePurchaseOrder $storeOrder)
    {
        $this->storeOrder->loadMissing(['store', 'items.product']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Incoming Order #{$this->storeOrder->po_number} — {$this->storeOrder->store?->store_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.incoming-store-order',
            with: ['storeOrder' => $this->storeOrder],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('warehouse.store-orders.pdf', ['storeOrder' => $this->storeOrder])
            ->setPaper('a4', 'portrait');

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                "Order-{$this->storeOrder->po_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
