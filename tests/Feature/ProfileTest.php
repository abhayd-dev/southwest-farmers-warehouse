<?php

namespace Tests\Feature;

use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use MakesWarehouseUsers;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($this->superAdmin())->get('/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Renamed User', 'email' => 'renamed@test.local', 'phone' => '5551234'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed User', $user->name);
        $this->assertSame('renamed@test.local', $user->email);
    }

    public function test_profile_requires_a_name(): void
    {
        $this->actingAs($this->superAdmin())
            ->patch('/profile', ['name' => '', 'email' => 'x@test.local'])
            ->assertSessionHasErrors();
    }
}
