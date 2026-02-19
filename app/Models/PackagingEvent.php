<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingEvent extends Model
{
    protected $fillable = [
        'free_weight_product_id',
        'package_id',
        'employee_id',
        'bulk_weight_reduced',
        'packages_created',
        'event_date',
        'notes'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'bulk_weight_reduced' => 'decimal:2',
    ];

    public function freeWeightProduct()
    {
        return $this->belongsTo(FreeWeightProduct::class);
    }

    public function package()
    {
        return $this->belongsTo(FreeWeightPackage::class, 'package_id');
    }

    public function employee()
    {
        return $this->belongsTo(WareUser::class, 'employee_id');
    }
}
