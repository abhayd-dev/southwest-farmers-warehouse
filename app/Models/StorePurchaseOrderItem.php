<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StorePurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_po_id',
        'product_id',
        'requested_qty',
        'dispatched_qty',
        'pending_qty',
        'status',
        'rejection_reason',
    ];

    // Status constants
    const STATUS_PENDING    = 'pending';
    const STATUS_APPROVED   = 'approved';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_REJECTED   = 'rejected';

    // Relationships
    public function storePurchaseOrder()
    {
        return $this->belongsTo(StorePurchaseOrder::class, 'store_po_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Computed: remaining qty to dispatch
    public function getRemainingQtyAttribute()
    {
        return $this->requested_qty - $this->dispatched_qty;
    }
}
