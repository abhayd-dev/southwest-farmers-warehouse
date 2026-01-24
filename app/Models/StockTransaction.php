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
        'remarks',
        'store_id',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====

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

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // ===== SCOPES =====

    /**
     * Get transactions for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get transactions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get warehouse transactions.
     */
    public function scopeWarehouse($query)
    {
        return $query->where('warehouse_id', 1);
    }

    /**
     * Get store transactions.
     */
    public function scopeStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get transactions by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('ware_user_id', $userId);
    }

    /**
     * Get transactions within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get recent transactions.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get inbound transactions.
     */
    public function scopeInbound($query)
    {
        return $query->whereIn('type', ['purchase_in', 'transfer_in', 'recall_in', 'return']);
    }

    /**
     * Get outbound transactions.
     */
    public function scopeOutbound($query)
    {
        return $query->whereIn('type', ['transfer_out', 'sale_out', 'recall_out', 'damage', 'adjustment']);
    }

    // ===== METHODS =====

    /**
     * Get transaction type label.
     */
    public function getTypeLabel()
    {
        $labels = [
            'purchase_in' => 'Purchase',
            'transfer_out' => 'Transfer Out',
            'transfer_in' => 'Transfer In',
            'sale_out' => 'Sale',
            'recall_out' => 'Recall Out',
            'recall_in' => 'Recall In',
            'adjustment' => 'Adjustment',
            'damage' => 'Damage',
            'return' => 'Return'
        ];
        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get transaction direction (in/out).
     */
    public function getDirection()
    {
        return $this->quantity_change > 0 ? 'in' : 'out';
    }

    /**
     * Check if transaction is inbound.
     */
    public function isInbound()
    {
        return in_array($this->type, ['purchase_in', 'transfer_in', 'recall_in', 'return']);
    }

    /**
     * Check if transaction is outbound.
     */
    public function isOutbound()
    {
        return in_array($this->type, ['transfer_out', 'sale_out', 'recall_out', 'damage', 'adjustment']);
    }
}