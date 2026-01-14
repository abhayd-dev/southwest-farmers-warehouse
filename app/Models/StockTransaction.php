<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'product_batch_id',
        'ware_user_id',
        'type',
        'quantity_change',
        'running_balance',
        'reference_id',
        'remarks'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(WareUser::class, 'ware_user_id');
    }
    
}