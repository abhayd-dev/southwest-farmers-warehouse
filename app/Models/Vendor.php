<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'lead_time_days',
        'rating',
        'on_time_delivery_rate',
        'total_orders_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
    ];

    // Scope to get only active vendors
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
