<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_request_id',
        'product_id',
        'quantity',
        'dispatched_quantity',
        'received_quantity',
        'unit_cost',
        'total_cost'
    ];

    public function stockRequest()
    {
        return $this->belongsTo(StockRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
