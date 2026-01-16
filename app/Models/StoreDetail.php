<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_details';

    protected $fillable = [
        'warehouse_id',
        'store_user_id',
        'store_name',
        'store_code',
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

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * The warehouse this store belongs to.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * The manager (Store Super Admin) assigned to this store.
     */
    public function manager()
    {
        return $this->belongsTo(StoreUser::class, 'store_user_id');
    }

    /**
     * The stock/inventory items available at this store.
     */
    public function stocks()
    {
        return $this->hasMany(StoreStock::class, 'store_id');
    }
}