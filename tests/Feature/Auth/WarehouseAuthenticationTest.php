<?php

namespace Tests\Feature\Auth;

use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

class WarehouseAuthenticationTest extends TestCase
{
    use MakesWarehouseUsers;

    public function test_login_screen_renders_and_asks_for_login_id(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login ID');
    }

    public function test_guest_is_redirected_to_login_from_root_and_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_with_login_id_and_password(): void
    {
        $user = $this->superAdmin(['emp_code' => 'EMP-1001']);

        $this->post('/login', ['emp_code' => 'EMP-1001', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->superAdmin(['emp_code' => 'EMP-1002']);

        $this->from('/login')
            ->post('/login', ['emp_code' => 'EMP-1002', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('emp_code');

        $this->assertGuest();
    }

    public function test_login_id_is_not_case_sensitive(): void
    {
        // Client 9/11 list, item 7: Login ID should not be case-sensitive
        // (was pinned as case-sensitive here until the client decided).
        $this->superAdmin(['emp_code' => 'EMP-1003']);

        $this->post('/login', ['emp_code' => 'emp-1003', 'password' => 'password']);

        $this->assertAuthenticated();
    }

    public function test_user_can_log_out(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
