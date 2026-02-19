<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\StoreDetail;
use App\Models\ProductMarketPrice;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function stores()
    {
        return $this->belongsToMany(StoreDetail::class, 'store_markets', 'market_id', 'store_id');
    }

    public function productPrices()
    {
        return $this->hasMany(ProductMarketPrice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
