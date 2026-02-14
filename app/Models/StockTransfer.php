<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function fromStore()
    {
        return $this->belongsTo(StoreDetail::class, 'from_store_id');
    }
    public function toStore()
    {
        return $this->belongsTo(StoreDetail::class, 'to_store_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(StoreUser::class, 'created_by');
    }
}
