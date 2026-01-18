<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRole extends Model
{
    protected $table = 'store_roles';
    protected $fillable = ['name', 'guard_name'];
}