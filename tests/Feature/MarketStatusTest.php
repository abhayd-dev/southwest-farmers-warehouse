<?php

namespace Tests\Feature;

use App\Models\Market;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/** Markets list: the status switch answers with a message the page shows as a toast. */
class MarketStatusTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    public function test_switching_a_market_off_and_on_returns_a_message_for_the_toast(): void
    {
        $market = Market::find($this->insertRow('markets', ['name' => 'HOUSTON MARKET', 'is_active' => true]));
        $admin = $this->superAdmin();

        $this->actingAs($admin)->postJson(route('warehouse.markets.status'), ['id' => $market->id, 'status' => 0])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Market "HOUSTON MARKET" deactivated.']);
        $this->assertFalse($market->refresh()->is_active);

        $this->actingAs($admin)->postJson(route('warehouse.markets.status'), ['id' => $market->id, 'status' => 1])
            ->assertJson(['success' => true, 'message' => 'Market "HOUSTON MARKET" activated.']);
        $this->assertTrue($market->refresh()->is_active);
    }

    public function test_an_unknown_market_is_a_clear_error_not_a_silent_success(): void
    {
        $this->actingAs($this->superAdmin())->postJson(route('warehouse.markets.status'), ['id' => 999999, 'status' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id' => 'This market no longer exists. Please refresh the page.']);
    }
}
