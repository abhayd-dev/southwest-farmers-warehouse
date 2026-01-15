<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WareUser extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'ware_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'designation',
        'emp_code',
        'address',
        'profile_image',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'is_active' => 'boolean'];

    // --- ROLES & PERMISSIONS LOGIC ---

    public function roles()
    {
        return $this->morphToMany(WareRole::class, 'model', 'ware_model_has_roles', 'model_id', 'role_id');
    }

    public function permissions()
    {
        return $this->morphToMany(WarePermission::class, 'model', 'ware_model_has_permissions', 'model_id', 'permission_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if user has a specific permission (via Role or Direct)
     */
    public function hasPermission($permissionName)
    {
        // 1. Check direct permissions
        if ($this->permissions->contains('name', $permissionName)) {
            return true;
        }

        // 2. Check permissions via roles
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Super Admin Bypass
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin');
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }

        return asset('assets/images/default-avatar.png');
    }
}
