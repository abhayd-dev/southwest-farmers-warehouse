<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Vendor;
use App\Models\PurchaseOrderItem;
use App\Models\WareUser;
use App\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'po_number',
        'vendor_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'total_amount',
        'tax_amount',
        'other_costs',
        'status',
        'payment_status',
        'notes',
        'created_by',
        'approved_by',
        'approval_email',
        'approval_status',
        'approved_by_email',
        'approved_at',
        'approval_reason'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ORDERED = 'ordered';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(WareUser::class, 'created_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function getProgressAttribute()
    {
        $totalReq = $this->items->sum('requested_quantity');
        if ($totalReq == 0) return 0;
        $totalRec = $this->items->sum('received_quantity');
        return round(($totalRec / $totalReq) * 100);
    }

    // Approval methods
    public function approve($approverEmail, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_ORDERED,
            'approval_status' => 'approved',
            'approved_by_email' => $approverEmail,
            'approved_at' => now(),
            'approval_reason' => $reason,
        ]);
    }

    public function reject($approverEmail, $reason)
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by_email' => $approverEmail,
            'approved_at' => now(),
            'approval_reason' => $reason,
        ]);
    }

    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }
}
