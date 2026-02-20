<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Market;

class ProductMarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'market_id',
        'cost_price',
        'sale_price',
        'promotion_price',
        'promotion_start_date',
        'promotion_end_date',
        'margin_percent',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'promotion_price' => 'decimal:2',
        'promotion_start_date' => 'datetime',
        'promotion_end_date' => 'datetime',
        'margin_percent' => 'decimal:2',
    ];


    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
