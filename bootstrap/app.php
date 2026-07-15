<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies so Railway's load balancer forwards HTTPS correctly.
        // Without this, signed URL validation fails (403 INVALID SIGNATURE) because
        // Laravel sees the internal HTTP request instead of the original HTTPS URL.
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PREFIX
        );

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Validation exceptions should propagate normally so users see validation errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            // Authentication exceptions should propagate to handle guest redirects
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            // Log all unhandled exceptions for debugging purposes
            \Illuminate\Support\Facades\Log::error('Unhandled Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation']),
            ]);

            // Determine if AJAX or API request
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ], 500);
            }

            // Check if the exception is an HTTP exception (e.g. 404, 403) and not 500
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                if ($e->getStatusCode() !== 500) {
                    return null; // let default handler render 404, 403, etc.
                }
            }

            // Render custom 500 error page
            return response()->view('errors.500', [], 500);
        });
    })->create();
