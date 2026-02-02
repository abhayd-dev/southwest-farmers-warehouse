<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAudit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(StockAuditItem::class);
    }

    public function initiator()
    {
        return $this->belongsTo(WareUser::class, 'initiated_by');
    }
}