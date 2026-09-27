<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Stock request page > "Stock In" (Purchase In, Direct Stock). The popup sends
 * product, quantity, reference and remarks; batch/cost are optional extras.
 */
class StockRequestPurchaseInTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    public function test_the_popup_fields_alone_add_stock_even_for_a_product_with_no_stock_yet(): void
    {
        $product = $this->insertRow('products', ['product_name' => 'FARMERS- CHOPPED SPINACH', 'store_id' => null, 'cost_price' => 3.25]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('warehouse.stock-requests.purchase-in'), [
                'product_id' => $product, 'quantity' => 5, 'purchase_ref' => 'PO-1234', 'remarks' => 'Truck delivery',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Added 5 units of FARMERS- CHOPPED SPINACH to warehouse stock.']);

        $this->assertEquals(5, DB::table('product_stocks')->where('product_id', $product)->where('warehouse_id', 1)->value('quantity'));
        $batch = DB::table('product_batches')->where('product_id', $product)->first();
        $this->assertStringStartsWith('PUR-', $batch->batch_number);
        $this->assertEquals(3.25, $batch->cost_price); // the product's own cost
        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $product, 'type' => 'purchase_in', 'quantity_change' => 5, 'reference_id' => 'PO-1234', 'remarks' => 'Truck delivery',
        ]);
    }

    public function test_batch_number_and_cost_are_used_when_given_and_stock_adds_up(): void
    {
        $product = $this->insertRow('products', ['product_name' => 'Rice', 'store_id' => null, 'cost_price' => 1]);
        $this->insertRow('product_stocks', ['product_id' => $product, 'warehouse_id' => 1, 'quantity' => 10]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('warehouse.stock-requests.purchase-in'), [
                'product_id' => $product, 'quantity' => 4, 'batch_number' => 'LOT-77', 'cost_price' => 2.5,
            ])->assertOk();

        $this->assertEquals(14, DB::table('product_stocks')->where('product_id', $product)->value('quantity'));
        $this->assertDatabaseHas('product_batches', ['product_id' => $product, 'batch_number' => 'LOT-77', 'cost_price' => 2.5, 'quantity' => 4]);
    }

    public function test_missing_quantity_is_the_only_error_shown(): void
    {
        $product = $this->insertRow('products', ['product_name' => 'Rice', 'store_id' => null]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('warehouse.stock-requests.purchase-in'), ['product_id' => $product])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJsonMissingValidationErrors(['batch_number', 'cost_price']);
    }
}
