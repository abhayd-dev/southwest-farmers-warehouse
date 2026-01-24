<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'store_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'cost_price',
        'quantity',
        'damaged_quantity',
        'is_active'
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class, 'product_batch_id');
    }

    // ===== SCOPES =====

    /**
     * Get only active batches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get only batches with stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Get warehouse batches.
     */
    public function scopeWarehouse($query)
    {
        return $query->where('warehouse_id', 1);
    }

    /**
     * Get store batches.
     */
    public function scopeStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get expiring soon batches.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>', now())
                     ->orderBy('expiry_date');
    }

    /**
     * Get expired batches.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Get damaged batches.
     */
    public function scopeDamaged($query)
    {
        return $query->where('damaged_quantity', '>', 0);
    }

    /**
     * Get ordered by FIFO (oldest first by manufacturing date).
     */
    public function scopeFifo($query)
    {
        return $query->orderBy('manufacturing_date')->orderBy('expiry_date');
    }

    // ===== METHODS =====

    /**
     * Get days until expiry.
     */
    public function getDaysToExpiry()
    {
        if (!$this->expiry_date) return null;
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if batch is expired.
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    /**
     * Check if batch is expiring soon (within 30 days).
     */
    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) return false;
        $daysLeft = $this->getDaysToExpiry();
        return $daysLeft !== null && $daysLeft > 0 && $daysLeft <= $days;
    }

    /**
     * Get batch value.
     */
    public function getValue()
    {
        return $this->quantity * $this->cost_price;
    }

    /**
     * Get usable quantity (excluding damaged).
     */
    public function getUsableQuantity()
    {
        return $this->quantity - $this->damaged_quantity;
    }

    /**
     * Get batch status for display.
     */
    public function getStatus()
    {
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringSoon(15)) {
            return 'critical';
        }
        if ($this->isExpiringSoon(30)) {
            return 'urgent';
        }
        if ($this->isExpiringSoon()) {
            return 'warning';
        }
        return 'normal';
    }

    /**
     * Get batch status badge HTML.
     */
    public function getStatusBadge()
    {
        $statuses = [
            'expired' => '<span class="badge bg-danger">Expired</span>',
            'critical' => '<span class="badge bg-danger">Critical (' . $this->getDaysToExpiry() . ' days)</span>',
            'urgent' => '<span class="badge bg-warning">Urgent (' . $this->getDaysToExpiry() . ' days)</span>',
            'warning' => '<span class="badge bg-info">Warning (' . $this->getDaysToExpiry() . ' days)</span>',
            'normal' => '<span class="badge bg-success">Normal</span>',
        ];
        return $statuses[$this->getStatus()] ?? $statuses['normal'];
    }

    /**
     * Move batch to store.
     */
    public function moveToStore($storeId)
    {
        return $this->update(['store_id' => $storeId]);
    }

    /**
     * Return batch to warehouse.
     */
    public function returnToWarehouse()
    {
        return $this->update(['store_id' => null, 'warehouse_id' => 1]);
    }
}