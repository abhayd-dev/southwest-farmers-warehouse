<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarePermission extends Model
{
    protected $table = 'ware_permissions';
    protected $fillable = ['name', 'guard_name'];

    public function roles()
    {
        return $this->belongsToMany(WareRole::class, 'ware_role_has_permissions', 'permission_id', 'role_id');
    }
}