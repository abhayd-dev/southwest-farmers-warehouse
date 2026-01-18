<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class StoreUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'store_users';

    protected $fillable = [
        'store_id', 
        'name',
        'email',
        'password',
        'phone',
        'designation',
        'parent_id',
        'profile_image',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function managedStore()
    {
        return $this->hasOne(StoreDetail::class, 'store_user_id');
    }

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    public function parent()
    {
        return $this->belongsTo(StoreUser::class, 'parent_id');
    }

    public function subordinates()
    {
        return $this->hasMany(StoreUser::class, 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            WareRole::class, 
            'store_model_has_roles', 
            'model_id', 
            'role_id'
        )->wherePivot('model_type', self::class);
    }

    public function getRoleNameAttribute()
    {
        $role = DB::table('store_model_has_roles')
            ->join('store_roles', 'store_model_has_roles.role_id', '=', 'store_roles.id')
            ->where('store_model_has_roles.model_id', $this->id)
            ->where('store_model_has_roles.model_type', self::class)
            ->value('store_roles.name');

        return $role ?? 'No Role';
    }

    public function isStoreAdmin()
    {
        return $this->managedStore()->exists();
    }
}