<?php

namespace Tests\Feature;

use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * /debug-logs and /clear-cache used to be reachable by anyone on the internet.
 */
class OperationalUtilityRoutesTest extends TestCase
{
    use MakesWarehouseUsers;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/debug-logs')->assertRedirect(route('login'));
        $this->get('/clear-cache')->assertRedirect(route('login'));
    }

    public function test_non_super_admins_are_forbidden(): void
    {
        $user = $this->userWithPermissions(['view_products']);

        $this->actingAs($user)->get('/debug-logs')->assertForbidden();
        $this->actingAs($user)->get('/clear-cache')->assertForbidden();
    }

    public function test_super_admin_can_use_them(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/clear-cache')->assertOk()->assertSee('Cache cleared');
        $this->actingAs($admin)->get('/debug-logs')->assertOk();
    }

    public function test_log_output_is_html_escaped(): void
    {
        $logFile = storage_path('logs/laravel.log');
        $existing = file_exists($logFile) ? file_get_contents($logFile) : null;
        file_put_contents($logFile, "[test] <script>alert(1)</script>\n", FILE_APPEND);

        try {
            $this->actingAs($this->superAdmin())->get('/debug-logs')
                ->assertOk()
                ->assertDontSee('<script>alert(1)</script>', false);
        } finally {
            $existing === null ? @unlink($logFile) : file_put_contents($logFile, $existing);
        }
    }
}
