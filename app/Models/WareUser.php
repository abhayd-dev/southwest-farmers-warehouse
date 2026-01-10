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
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
