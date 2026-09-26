<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'requested_quantity',
        'received_quantity',
        'unit_cost',
        'tax_percent',
        'total_cost',
        'receiving_unit_cost'
    ];

    // Decimal quantities (QA: receiving by weight). Cast to float so whole
    // numbers still display as "100", not "100.00".
    protected $casts = [
        'requested_quantity' => 'float',
        'received_quantity' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function getPendingQuantityAttribute()
    {
        return max(0, round($this->requested_quantity - $this->received_quantity, 2));
    }
}
