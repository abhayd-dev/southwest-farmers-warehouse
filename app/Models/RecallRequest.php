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

    const STATUS_PENDING_STORE_APPROVAL = 'pending_store_approval';
    const STATUS_APPROVED_BY_STORE = 'approved_by_store';
    const STATUS_PARTIAL_APPROVED = 'partial_approved';
    const STATUS_REJECTED_BY_STORE = 'rejected_by_store';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_RECEIVED = 'received';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

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
}