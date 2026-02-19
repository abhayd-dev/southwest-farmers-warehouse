<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this line

class StorePurchaseOrder extends Model
{
    use HasFactory; // Added this line

    protected $fillable = [
        'po_number',
        'store_id',
        'request_date',
        'status',
        'admin_note',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    public function items()
    {
        return $this->hasMany(StorePurchaseOrderItem::class, 'store_po_id');
    }

    public function creator()
    {
        return $this->belongsTo(StoreUser::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(WareUser::class, 'approved_by');
    }

    // Methods
    public static function generatePONumber()
    {
        $lastPO = self::latest('id')->first();
        $number = $lastPO ? (int)substr($lastPO->po_number, 4) + 1 : 1;
        return 'SPO-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function approve($userId)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($userId, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'admin_note' => $reason,
        ]);
    }
}
