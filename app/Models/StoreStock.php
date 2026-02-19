<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreStock extends Model
{
    use HasFactory;

    protected $table = 'store_stocks';

    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
        'selling_price',
        'min_stock',
        'max_stock',
    ];

    protected $casts = [
        'quantity' => 'integer', // or 'decimal:2' if you use fractional units
        'selling_price' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}