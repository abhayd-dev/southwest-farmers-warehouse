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
        'fulfilled_quantity', // New: Track partial shipments
        'status', // pending, approved, partial, completed, rejected
        'admin_note'
    ];

    // Status Constants for clean code
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved'; 
    const STATUS_PARTIAL = 'partial';   // Sent some, waiting for more
    const STATUS_DISPATCHED = 'dispatched'; // In Transit (Raaste mein)
    const STATUS_COMPLETED = 'completed'; // Store received full stock
    const STATUS_REJECTED = 'rejected';



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

    public function isLowStockInStore()
    {
        if (!$this->storeStock) {
            return true;
        }
        return $this->storeStock->quantity < 10; 
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL, self::STATUS_APPROVED]);
    }

    public function scopeUrgent($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}