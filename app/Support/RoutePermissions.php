<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Resolves which permission(s) a named route requires, from
 * config/route_permissions.php. Shared by the middleware, the audit command
 * and the tests so they can never disagree.
 */
class RoutePermissions
{
    public const MODE_OFF = 'off';
    public const MODE_LOG = 'log';
    public const MODE_ENFORCE = 'enforce';

    public static function mode(): string
    {
        $mode = strtolower((string) config('route_permissions.mode', self::MODE_LOG));

        return in_array($mode, [self::MODE_OFF, self::MODE_LOG, self::MODE_ENFORCE], true) ? $mode : self::MODE_LOG;
    }

    /** True when the route is deliberately reachable by any authenticated user. */
    public static function isOpen(string $routeName): bool
    {
        return Str::is((array) config('route_permissions.open', []), $routeName);
    }

    /**
     * @return string[]|null  "any of" permission names, or null when the route
     *                        needs no permission (open or unmapped).
     */
    public static function requiredFor(string $routeName): ?array
    {
        if (self::isOpen($routeName)) {
            return null;
        }

        foreach ((array) config('route_permissions.map', []) as $pattern => $permissions) {
            if (Str::is($pattern, $routeName)) {
                return (array) $permissions;
            }
        }

        return null;
    }

    /** True when the route is neither explicitly open nor mapped (i.e. ungoverned). */
    public static function isUnmapped(string $routeName): bool
    {
        return !self::isOpen($routeName) && self::requiredFor($routeName) === null;
    }

    public static function allows($user, array $anyOf): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($anyOf as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
