<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class FastMovingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sales_velocity',
        'stockout_frequency',
        'dispatch_volume',
        'is_fast_moving',
        'calculated_at',
    ];

    protected $casts = [
        'sales_velocity' => 'decimal:2',
        'is_fast_moving' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function markAsFastMoving()
    {
        $this->update([
            'is_fast_moving' => true,
            'calculated_at' => now(),
        ]);
    }

    public function scopeFastMoving($query)
    {
        return $query->where('is_fast_moving', true);
    }
}
