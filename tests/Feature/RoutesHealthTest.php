<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Cheap guards that catch the most common breakage when controllers are moved,
 * renamed or split: a route pointing at a class/method that doesn't exist, or a
 * page that errors on a completely empty database.
 */
class RoutesHealthTest extends TestCase
{
    use MakesWarehouseUsers;

    public function test_every_controller_route_points_at_a_real_class_and_method(): void
    {
        $broken = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction('controller');
            if (!is_string($action)) {
                continue;
            }

            [$class, $method] = str_contains($action, '@') ? explode('@', $action) : [$action, '__invoke'];

            if (!class_exists($class)) {
                $broken[] = "{$route->uri()} -> missing class {$class}";
            } elseif (!method_exists($class, $method)) {
                $broken[] = "{$route->uri()} -> {$class}@{$method} does not exist";
            }
        }

        $this->assertSame([], $broken, implode("\n", $broken));
    }

    public function test_every_parameterless_page_renders_without_a_server_error(): void
    {
        $admin = $this->superAdmin();
        $errors = [];

        foreach (Route::getRoutes() as $route) {
            $isPlainGet = in_array('GET', $route->methods(), true)
                && !str_contains($route->uri(), '{')
                && in_array('auth', $route->gatherMiddleware(), true)
                && $route->getName();

            if (!$isPlainGet) {
                continue;
            }

            $status = $this->actingAs($admin)->get('/' . ltrim($route->uri(), '/'))->getStatusCode();

            if ($status >= 500) {
                $errors[] = "{$route->getName()} (/{$route->uri()}) -> HTTP {$status}";
            }
        }

        $this->assertSame([], $errors, "Pages that fail on an empty database:\n" . implode("\n", $errors));
    }
}
