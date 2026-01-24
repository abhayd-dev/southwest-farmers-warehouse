<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PODelayedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $po;
    public $level;

    public function __construct(PurchaseOrder $po, $level)
    {
        $this->po = $po;
        $this->level = $level;
    }

    public function build()
    {
        $subject = "⚠️ URGENT: PO #{$this->po->po_number} Delayed - Level {$this->level} Alert";
        
        return $this->subject($subject)
                    ->view('emails.po_delayed');
    }
}