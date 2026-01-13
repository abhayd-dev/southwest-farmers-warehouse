<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function subcategories()
    {
        return $this->hasMany(ProductSubcategory::class, 'category_id');
    }

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
