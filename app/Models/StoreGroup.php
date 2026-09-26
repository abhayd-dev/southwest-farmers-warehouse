<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A named set of stores (e.g. a region). Staff assigned to a group on the
 * Store side -- typically a Regional Manager -- can switch between its stores.
 */
class StoreGroup extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function stores()
    {
        return $this->hasMany(StoreDetail::class, 'store_group_id');
    }

    public function staff()
    {
        return $this->hasMany(StoreUser::class, 'store_group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
