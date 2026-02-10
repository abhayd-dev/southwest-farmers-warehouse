<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ProductSubcategory extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'icon',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class, 'subcategory_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'subcategory_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}