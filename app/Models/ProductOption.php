<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'option_name',
        'sku',
        'barcode',
        'unit',
        'tax_percent',
        'cost_price',
        'base_price',
        'mrp',
        'description',
        'icon',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductSubcategory::class);
    }
}
