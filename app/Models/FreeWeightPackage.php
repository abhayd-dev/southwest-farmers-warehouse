<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeWeightPackage extends Model
{
    protected $fillable = [
        'free_weight_product_id',
        'target_product_id', // The sellable product (e.g., 10lb bag SKU)
        'package_name',
        'package_size',
        'unit',
        'sku',
        'barcode',
        'quantity_created'
    ];

    public function freeWeightProduct()
    {
        return $this->belongsTo(FreeWeightProduct::class);
    }

    public function targetProduct()
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }
}
