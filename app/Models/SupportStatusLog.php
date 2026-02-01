<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportStatusLog extends Model
{
    protected $guarded = [];

    // FIX: Define foreign key 'ticket_id' explicitly
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function changedBy()
    {
        return $this->morphTo();
    }
}