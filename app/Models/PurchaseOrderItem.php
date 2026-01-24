<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'product_id', 'requested_quantity', 
        'received_quantity', 'unit_cost', 'tax_percent', 'total_cost'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute()
    {
        return max(0, $this->requested_quantity - $this->received_quantity);
    }
}