<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PalletItem extends Model
{
    protected $fillable = [
        'pallet_id',
        'product_id',
        'quantity',
        'weight_per_unit',
        'total_weight',
    ];

    protected $casts = [
        'weight_per_unit' => 'decimal:2',
        'total_weight'    => 'decimal:2',
    ];

    public function pallet()
    {
        return $this->belongsTo(Pallet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
