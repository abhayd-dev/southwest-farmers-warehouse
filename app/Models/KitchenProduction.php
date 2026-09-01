<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenProduction extends Model
{
    protected $fillable = [
        'menu_item_id',
        'kitchen_location_id',
        'ware_user_id',
        'quantity_made',
        'quantity_unit',
        'yield_plates',
        'daily_target',
        'produced_at',
        'notes',
    ];

    protected $casts = [
        'produced_at' => 'datetime',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function kitchenLocation()
    {
        return $this->belongsTo(KitchenLocation::class);
    }

    public function wareUser()
    {
        return $this->belongsTo(WareUser::class);
    }
}
