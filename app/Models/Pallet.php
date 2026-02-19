<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\StockTransfer;
use App\Models\Department;
use App\Models\PalletItem;

class Pallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'pallet_number',
        'department_id',
        'total_weight',
        'max_weight',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';

    // Relationships
    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(PalletItem::class);
    }

    public function storePO()
    {
        return $this->belongsTo(StorePurchaseOrder::class, 'store_po_id');
    }

    // Methods
    public static function generatePalletNumber()
    {
        $lastPallet = self::latest('id')->first();
        $number = $lastPallet ? (int)substr($lastPallet->pallet_number, 4) + 1 : 1;
        return 'PLT-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function addItem($productId, $quantity, $weightPerUnit)
    {
        $totalWeight = $quantity * $weightPerUnit;
        
        if (($this->total_weight + $totalWeight) > $this->max_weight) {
            throw new \Exception("Adding this item would exceed pallet weight limit of {$this->max_weight} lbs");
        }

        $item = $this->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'weight_per_unit' => $weightPerUnit,
            'total_weight' => $totalWeight,
        ]);

        $this->increment('total_weight', $totalWeight);

        return $item;
    }

    public function isOverweight()
    {
        return $this->total_weight > $this->max_weight;
    }

    public function remainingCapacity()
    {
        return $this->max_weight - $this->total_weight;
    }
}
