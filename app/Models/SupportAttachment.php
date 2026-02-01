<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAttachment extends Model
{
    protected $guarded = [];

    // FIX: Define foreign key 'ticket_id' explicitly
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function message()
    {
        return $this->belongsTo(SupportMessage::class, 'message_id');
    }
}