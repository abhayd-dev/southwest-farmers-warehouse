<?php

namespace App\Support;

use App\Models\WarePermission;
use Illuminate\Support\Facades\Cache;

class PermissionCatalog
{
    private const CACHE_KEY = 'ware_permission_names';
    private const CACHE_SECONDS = 300;

    /** @return string[] every permission name that exists in ware_permissions */
    public static function names(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => WarePermission::pluck('name')->all());
        } catch (\Throwable) {
            return [];
        }
    }

    public static function has(string $ability): bool
    {
        return in_array($ability, self::names(), true);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
