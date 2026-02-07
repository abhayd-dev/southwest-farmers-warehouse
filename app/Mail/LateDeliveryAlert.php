<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LateDeliveryAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseOrders;

    public function __construct($purchaseOrders)
    {
        $this->purchaseOrders = $purchaseOrders;
    }

    public function build()
    {
        return $this->subject('🚨 Alert: Late Vendor Deliveries Detected')
                    ->view('emails.late_delivery');
    }
}