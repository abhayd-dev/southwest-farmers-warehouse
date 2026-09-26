<?php

namespace Tests\Feature;

use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/** Client 9/11 list, item 7: the warehouse Login ID should not be case-sensitive. */
class LoginIdCaseInsensitiveTest extends TestCase
{
    use MakesWarehouseUsers;

    public function test_login_id_matches_regardless_of_case_and_spaces(): void
    {
        $this->makeUser(['emp_code' => 'NKABANI', 'password' => bcrypt('secret-pass')]);

        $this->post(route('login'), ['emp_code' => '  nkabani ', 'password' => 'secret-pass'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_wrong_password_still_fails(): void
    {
        $this->makeUser(['emp_code' => 'NKABANI', 'password' => bcrypt('secret-pass')]);

        $this->post(route('login'), ['emp_code' => 'nkabani', 'password' => 'nope'])
            ->assertSessionHasErrors('emp_code');
        $this->assertGuest();
    }
}
