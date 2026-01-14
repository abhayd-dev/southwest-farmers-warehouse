<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WareRole extends Model
{
    protected $table = 'ware_roles';
    protected $fillable = ['name', 'guard_name'];

    public function permissions()
    {
        return $this->belongsToMany(WarePermission::class, 'ware_role_has_permissions', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->morphedByMany(WareUser::class, 'model', 'ware_model_has_roles', 'role_id', 'model_id');
    }
}