<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\StoreDetail;

class StoreProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'cost_price',
        'sale_price',
        'margin_percent',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'margin_percent' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }
}
