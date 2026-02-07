<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WareSetting extends Model
{
    protected $guarded = [];

    // Clear cache on save/update
    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('ware_settings');
        });
    }

    public static function get_value($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}