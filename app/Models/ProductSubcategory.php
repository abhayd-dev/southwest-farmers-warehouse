<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSubcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'code',
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