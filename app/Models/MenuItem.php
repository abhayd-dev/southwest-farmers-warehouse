<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_category_id',
        'name',
        'description',
        'price',
        'image',
        'is_active',
        'is_pre_cooked',
        'daily_target_quantity',
        'is_catering_only',
        'advance_notice_days',
        'rush_fee_percentage',
        'available_days',
        'is_available_today',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pre_cooked' => 'boolean',
        'is_catering_only' => 'boolean',
        'is_available_today' => 'boolean',
        'available_days' => 'array',
        'price' => 'decimal:2',
        'rush_fee_percentage' => 'decimal:2',
    ];

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function productions()
    {
        return $this->hasMany(KitchenProduction::class);
    }

    public function leftoverLogs()
    {
        return $this->hasMany(LeftoverLog::class);
    }
}
