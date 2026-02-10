<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WareNotification extends Model
{
    protected $table = 'ware_notifications';

    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'url', 'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}