<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenShift extends Model
{
    protected $fillable = [
        'ware_user_id',
        'kitchen_location_id',
        'shift_date',
        'start_time',
        'end_time',
        'station',
        'status',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
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
