<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class StoreUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'store_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'designation',
        'parent_id',       // For hierarchy (e.g., Staff reports to Manager)
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


    /**
     * The store managed by this user (if they are a Manager).
     */
    public function managedStore()
    {
        return $this->hasOne(StoreDetail::class, 'store_user_id');
    }

    /**
     * Self-referencing relationship for hierarchy (Parent User).
     */
    public function parent()
    {
        return $this->belongsTo(StoreUser::class, 'parent_id');
    }

    /**
     * Self-referencing relationship for hierarchy (Subordinate Staff).
     */
    public function subordinates()
    {
        return $this->hasMany(StoreUser::class, 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the user is a Super Admin for the Store.
     * (Logic: If they are assigned as the 'store_user_id' in store_details)
     */
    public function isStoreAdmin()
    {
        return $this->managedStore()->exists();
    }
}