<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WareActivityLog extends Model
{
    protected $table = 'ware_activity_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'array', 
        'created_at' => 'datetime',
    ];


    public function causer()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }
}