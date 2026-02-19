<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeWeightProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bulk_weight',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'bulk_weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(WareDetail::class, 'warehouse_id');
    }

    public function packages()
    {
        return $this->hasMany(FreeWeightPackage::class);
    }

    public function packagingEvents()
    {
        return $this->hasMany(PackagingEvent::class);
    }
}
