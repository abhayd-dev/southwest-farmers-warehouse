<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Traits\HasBarcodeImage;

class ProductOption extends Model
{
    use LogsActivity, HasBarcodeImage;

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'option_name',
        'sku',
        'upc',
        'barcode',
        'unit',
        'tax_percent',
        'cost_price',
        'base_price',
        'mrp',
        'description',
        'icon',
        'is_active',
        'barcode_image',
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
