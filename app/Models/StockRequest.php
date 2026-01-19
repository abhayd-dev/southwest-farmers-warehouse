<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'requested_quantity',
        'fulfilled_quantity',
        'status',
        'admin_note',
        'store_payment_proof',
        'store_remarks',
        'warehouse_payment_proof',
        'warehouse_remarks',
        'verified_at',
        'purchase_ref'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DISPATCHED = 'dispatched'; // Warehouse sends items
    const STATUS_VERIFY_PAYMENT = 'verify_payment'; // Optional intermediate step if needed, but per prompt: Dispatched -> Verify Payment (Action) -> Completed
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    
    // Legacy support if needed
    const STATUS_PARTIAL = 'partial'; 

    protected $casts = [
        'verified_at' => 'datetime'
    ];

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function storeStock()
    {
        return $this->hasOne(StoreStock::class, 'product_id', 'product_id')
                    ->where('store_id', $this->store_id);
    }

    public function getPendingQuantityAttribute()
    {
        return max(0, $this->requested_quantity - $this->fulfilled_quantity);
    }

    public function isLowStock()
    {
        if (!$this->storeStock) {
            return true;
        }
        return $this->storeStock->quantity < 10;
    }
}