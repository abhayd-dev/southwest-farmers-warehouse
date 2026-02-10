<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ProductCategory extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'icon',
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
