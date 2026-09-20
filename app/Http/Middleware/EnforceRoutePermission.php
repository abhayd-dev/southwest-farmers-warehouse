<?php

namespace App\Http\Middleware;

use App\Support\RoutePermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnforceRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $mode = RoutePermissions::mode();
        $routeName = $request->route()?->getName();
        $user = $request->user();

        if ($mode === RoutePermissions::MODE_OFF || !$routeName || !$user) {
            return $next($request);
        }

        $required = RoutePermissions::requiredFor($routeName);

        if ($required === null || RoutePermissions::allows($user, $required)) {
            return $next($request);
        }

        if ($mode === RoutePermissions::MODE_ENFORCE) {
            abort(403, 'Unauthorized action. Required permission: ' . implode(' or ', $required));
        }

        Log::channel('permissions')->warning('permission would be denied', [
            'user_id' => $user->id,
            'user' => $user->name,
            'route' => $routeName,
            'method' => $request->method(),
            'url' => $request->path(),
            'requires_any_of' => $required,
        ]);

        return $next($request);
    }
}
