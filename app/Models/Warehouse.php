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
        'is_active',
    ];
}
