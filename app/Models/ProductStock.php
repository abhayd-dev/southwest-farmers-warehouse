<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'damaged_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}