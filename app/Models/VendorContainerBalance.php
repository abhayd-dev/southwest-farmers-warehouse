<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorContainerBalance extends Model
{
    protected $fillable = [
        'vendor_id',
        'pallet_count',
        'divider_count',
    ];

    protected $casts = [
        'pallet_count' => 'float',
        'divider_count' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
