<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMinMaxLevel extends Model
{
    protected $fillable = [
        'product_id',
        'min_level',
        'max_level',
        'reorder_quantity',
        'updated_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(WareUser::class, 'updated_by');
    }
}