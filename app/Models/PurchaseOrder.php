<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Vendor;
use App\Models\PurchaseOrderItem;
use App\Models\WareUser;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;

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
        'vendor_invoice_number',
        'invoice_document',
        'duties',
        'shipping_cost',
        'taxes',
        'transportation_cost',
        'demurrage',
        'cost_increase_approved',
        'status',
        'payment_status',
        'notes',
        'created_by',
        'approval_email',
        'approver_phone',
        'approval_status',
        'approved_by_email',
        'approved_at',
        'approval_reason',
        'vendor_response_status',
        'vendor_response_at',
        'vendor_denial_reason',
        'shipment_type',
        'over_receipt_status',
        'over_receipt_lines',
        'over_receipt_decided_by',
        'over_receipt_decided_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'approval_email_verified_at' => 'datetime',
        'vendor_response_at' => 'datetime',
        'over_receipt_lines' => 'array',
        'over_receipt_decided_at' => 'datetime',
    ];

    const SHIPMENT_TRUCK = 'truck';
    const SHIPMENT_CONTAINER = 'container';

    const OVER_RECEIPT_PENDING = 'pending';
    const OVER_RECEIPT_APPROVED = 'approved';
    const OVER_RECEIPT_REJECTED = 'rejected';

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ORDERED = 'ordered';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // approval_status values (separate from the fulfilment status above)
    const APPROVAL_DRAFT = 'draft';
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    /**
     * The list screen's status dropdown. "Draft", "Waiting for Approval" and
     * "Approved" are all fulfilment status = draft, told apart by approval_status.
     * With no tab (or "all"), completed and cancelled POs are hidden.
     */
    public function scopeForListTab($query, ?string $tab)
    {
        return match ($tab) {
            // "Open" = anything not completed or cancelled (client PDF 9/24, item 4).
            null, '', 'all', 'open' => $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]),
            'in_transit' => $query->where('status', self::STATUS_PARTIAL),
            'pending_approval' => $query->where('status', self::STATUS_DRAFT)->where('approval_status', self::APPROVAL_PENDING),
            'approved' => $query->where('status', self::STATUS_DRAFT)->where('approval_status', self::APPROVAL_APPROVED),
            // approval_status is NOT NULL and new POs are saved as 'draft'; the old
            // whereNull() here could never match, so this tab was always empty.
            'draft' => $query->where('status', self::STATUS_DRAFT)
                ->where(fn ($q) => $q->where('approval_status', self::APPROVAL_DRAFT)->orWhereNull('approval_status')),
            default => $query->where('status', $tab),
        };
    }

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
            'status' => 'draft',
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

    // Vendor response (acknowledge/deny) — separate from internal approval_status.
    public function vendorAcknowledge()
    {
        $this->update([
            'vendor_response_status' => 'acknowledged',
            'vendor_response_at' => now(),
            'vendor_denial_reason' => null,
        ]);
    }

    public function vendorDeny(string $reason)
    {
        $this->update([
            'vendor_response_status' => 'denied',
            'vendor_response_at' => now(),
            'vendor_denial_reason' => $reason,
        ]);
    }

    public function isVendorAcknowledged()
    {
        return $this->vendor_response_status === 'acknowledged';
    }

    public function isVendorDenied()
    {
        return $this->vendor_response_status === 'denied';
    }

    public function getInvoiceDocumentUrlAttribute(): ?string
    {
        if (!$this->invoice_document) {
            return null;
        }

        if (str_starts_with($this->invoice_document, 'http://') || str_starts_with($this->invoice_document, 'https://')) {
            return $this->invoice_document;
        }

        // Use r2 disk if configured, fallback to public disk
        $disk = config('filesystems.disks.r2') ? 'r2' : 'public';
        return Storage::disk($disk)->url($this->invoice_document);
    }

    public function getIsInvoiceDocumentImageAttribute(): bool
    {
        if (!$this->invoice_document) {
            return false;
        }

        $extension = strtolower(pathinfo($this->invoice_document, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg']);
    }
}
