<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super Admin bypass
        if ($user->roles->contains('name', 'Super Admin')) {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'Unauthorized action. Required Permission: ' . $permission);
        }

        return $next($request);
    }
}
