<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreStock extends Model
{
    use HasFactory;

    protected $table = 'store_stocks';

    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
        'price', // Store's specific selling price
        'alert_level' // If you added this in a later migration, keep it. If not, remove.
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
    ];

    /**
     * The store where this stock is held.
     */
    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    /**
     * The product item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}