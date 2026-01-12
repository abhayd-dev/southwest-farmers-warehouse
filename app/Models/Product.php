<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_option_id',
        'category_id',
        'subcategory_id',
        'product_name',
        'sku',
        'barcode',
        'unit',
        'tax_percent',
        'price',
        'cost_price',
        'is_active'
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductSubcategory::class);
    }
}
