<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = 'ware_details';

    protected $fillable = [
        'warehouse_name',
        'code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'latitude',
        'longitude',
        'is_active',
    ];

    /**
     * Accessor for 'name' attribute (maps to warehouse_name)
     */
    public function getNameAttribute()
    {
        return $this->warehouse_name;
    }

    /**
     * Get all stores associated with this warehouse.
     */
    public function stores()
    {
        return $this->hasMany(StoreDetail::class, 'warehouse_id');
    }

    /**
     * Get the staff/users working in this warehouse.
     */
    public function users()
    {
        return $this->hasMany(WareUser::class, 'warehouse_id');
    }
}