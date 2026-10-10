<?php

namespace Tests\Feature;

use App\Models\Sale;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Warehouse kitchen board follows the kitchen spec 5.2 flow: kitchen orders
 * only, one step at a time, Handoff before Completed, cancel needs a reason.
 */
class KitchenKdsTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    private function sale(string $invoice, bool $kitchenItem, string $status = 'New'): Sale
    {
        $store = $this->insertRow('store_details', ['store_name' => 'Bissonnet']);
        $sale = Sale::find($this->insertRow('sales', ['store_id' => $store, 'invoice_number' => $invoice, 'kitchen_status' => $status, 'created_at' => now()]));
        $this->insertRow('sale_items', $kitchenItem
            ? ['sale_id' => $sale->id, 'menu_item_id' => $this->insertRow('menu_items', ['name' => 'Egusi Soup', 'price' => 12]), 'quantity' => 1, 'price' => 12, 'total' => 12]
            : ['sale_id' => $sale->id, 'product_id' => $this->insertRow('products', ['product_name' => 'Rice 5lb']), 'quantity' => 1, 'price' => 5, 'total' => 5]);

        return $sale;
    }

    public function test_board_shows_kitchen_orders_only_with_handoff_column(): void
    {
        $this->sale('INV-FOOD', true);
        $this->sale('INV-GROCERY', false);

        $this->actingAs($this->superAdmin())->get(route('kitchen.kds.index'))
            ->assertOk()->assertSee('INV-FOOD')->assertDontSee('INV-GROCERY')->assertSee('Handoff')->assertSee('Bissonnet');
    }

    public function test_orders_move_one_step_at_a_time_through_handoff(): void
    {
        $sale = $this->sale('INV-1', true);
        $this->actingAs($admin = $this->superAdmin());
        $url = route('kitchen.kds.status', $sale);

        $this->postJson($url, ['status' => 'Ready'])->assertStatus(422);
        foreach (['Accepted', 'Preparing', 'Ready', 'Handoff', 'Completed'] as $step) {
            $this->postJson($url, ['status' => $step])->assertOk();
        }

        $sale->refresh();
        $this->assertSame('Completed', $sale->kitchen_status);
        $this->assertSame($admin->name, $sale->kitchen_status_changed_by);
    }

    public function test_cancel_needs_a_reason(): void
    {
        $sale = $this->sale('INV-2', true, 'Preparing');
        $this->actingAs($this->superAdmin());
        $url = route('kitchen.kds.status', $sale);

        $this->postJson($url, ['status' => 'Cancelled'])->assertStatus(422);
        $this->postJson($url, ['status' => 'Cancelled', 'reason' => 'Kitchen closed'])->assertOk();

        $this->assertSame('Kitchen closed', $sale->refresh()->kitchen_cancel_reason);
    }
}
