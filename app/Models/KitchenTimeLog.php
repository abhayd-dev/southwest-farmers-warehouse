<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenTimeLog extends Model
{
    protected $fillable = [
        'ware_user_id',
        'kitchen_location_id',
        'clock_in_at',
        'clock_out_at',
        'total_hours',
        'break_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'total_hours' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(WareUser::class, 'ware_user_id');
    }

    public function kitchenLocation()
    {
        return $this->belongsTo(KitchenLocation::class);
    }
}
