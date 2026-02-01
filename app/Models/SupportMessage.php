<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $guarded = [];

    // FIX: Define foreign key 'ticket_id' explicitly
    public function ticket() { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
    
    public function sender() { return $this->morphTo(); }
    
    public function attachments() { return $this->hasMany(SupportAttachment::class, 'message_id'); }
}