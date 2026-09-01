<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeftoverLog extends Model
{
    protected $fillable = [
        'menu_item_id',
        'kitchen_location_id',
        'quantity_produced',
        'quantity_sold',
        'quantity_leftover',
        'log_date',
        'notes',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function kitchenLocation()
    {
        return $this->belongsTo(KitchenLocation::class);
    }
}
