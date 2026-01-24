<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecallRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
        'dispatched_quantity',
        'received_quantity',
        'status',
        'reason',
        'reason_remarks',
        'store_remarks',
        'warehouse_remarks',
        'initiated_by',
        'approved_by_store_user_id',
        'received_by_ware_user_id',
    ];

    // Status Constants
    const STATUS_PENDING_STORE_APPROVAL = 'pending_store_approval';
    const STATUS_APPROVED_BY_STORE = 'approved_by_store';
    const STATUS_PARTIAL_APPROVED = 'partial_approved';
    const STATUS_REJECTED_BY_STORE = 'rejected_by_store';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_RECEIVED = 'received';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ===== RELATIONSHIPS =====

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreDetail::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(WareUser::class, 'initiated_by');
    }

    public function storeApprover(): BelongsTo
    {
        return $this->belongsTo(StoreUser::class, 'approved_by_store_user_id');
    }

    public function warehouseReceiver(): BelongsTo
    {
        return $this->belongsTo(WareUser::class, 'received_by_ware_user_id');
    }

    // ===== SCOPES =====

    /**
     * Get pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_STORE_APPROVAL);
    }

    /**
     * Get approved recalls.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED_BY_STORE)
                     ->orWhere('status', self::STATUS_PARTIAL_APPROVED);
    }

    /**
     * Get completed recalls.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Get rejected recalls.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED_BY_STORE);
    }

    /**
     * Get recalled by store.
     */
    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get recalled by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get recalls within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get initiated by user.
     */
    public function scopeInitiatedBy($query, $userId)
    {
        return $query->where('initiated_by', $userId);
    }

    // ===== METHODS =====

    /**
     * Get status label.
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING_STORE_APPROVAL => 'Pending Store Approval',
            self::STATUS_APPROVED_BY_STORE => 'Approved by Store',
            self::STATUS_PARTIAL_APPROVED => 'Partially Approved',
            self::STATUS_REJECTED_BY_STORE => 'Rejected by Store',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_PENDING_STORE_APPROVAL => 'warning',
            self::STATUS_APPROVED_BY_STORE => 'info',
            self::STATUS_PARTIAL_APPROVED => 'info',
            self::STATUS_REJECTED_BY_STORE => 'danger',
            self::STATUS_DISPATCHED => 'primary',
            self::STATUS_RECEIVED => 'success',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

}