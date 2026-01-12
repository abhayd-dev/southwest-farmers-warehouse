<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSubcategory extends Model
{

    protected $fillable = [
        'category_id',
        'name',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
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
