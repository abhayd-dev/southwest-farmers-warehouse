<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number', 'vendor_id', 'warehouse_id', 'order_date', 'expected_delivery_date',
        'total_amount', 'tax_amount', 'other_costs', 'status', 'payment_status',
        'notes', 'created_by', 'approved_by'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
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

    public function getProgressAttribute()
    {
        $totalReq = $this->items->sum('requested_quantity');
        if($totalReq == 0) return 0;
        $totalRec = $this->items->sum('received_quantity');
        return round(($totalRec / $totalReq) * 100);
    }
}