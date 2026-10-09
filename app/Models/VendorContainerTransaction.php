<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorContainerTransaction extends Model
{
    protected $fillable = [
        'vendor_id',
        'container_type',
        'direction',
        'quantity_change',
        'running_balance',
        'purchase_order_id',
        'ware_user_id',
        'remarks',
    ];

    protected $casts = [
        'quantity_change' => 'float',
        'running_balance' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function user()
    {
        return $this->belongsTo(WareUser::class, 'ware_user_id');
    }
}
