<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SupportTicket;
use App\Models\SupportMessage;

class SupportTicketReplied extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $msg;

    public function __construct(SupportTicket $ticket, SupportMessage $msg)
    {
        $this->ticket = $ticket;
        $this->msg = $msg;
    }

    public function build()
    {
        return $this->subject('New Reply on Ticket: ' . $this->ticket->ticket_number)
                    ->view('emails.support.replied');
    }
}