<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/** Issues found by the full-app QA sweep (2026-09-27). */
class QaSweepRegressionTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    public function test_a_missing_record_is_a_404_for_ajax_calls_too_not_a_500(): void
    {
        $this->actingAs($this->superAdmin())
            ->putJson(route('warehouse.stores.groups.update', 999999), ['name' => 'x'])
            ->assertNotFound();
    }

    public function test_a_forbidden_action_is_a_403_for_ajax_calls_too_not_a_500(): void
    {
        $po = $this->insertRow('purchase_orders', ['vendor_id' => 1, 'po_number' => 'PO-QA-1', 'order_date' => now()->toDateString(), 'status' => 'ordered']);

        $this->actingAs($this->superAdmin())
            ->postJson(route('warehouse.purchase-orders.mark-completed', $po))
            ->assertForbidden();
    }

    public function test_status_switches_reject_an_unknown_id_with_a_reason(): void
    {
        $admin = $this->superAdmin();
        foreach (['warehouse.categories.status', 'warehouse.subcategories.status', 'warehouse.product-options.status',
                  'warehouse.products.status', 'warehouse.departments.status', 'warehouse.vendors.status', 'warehouse.staff.status'] as $route) {
            $this->actingAs($admin)->postJson(route($route), ['id' => 999999, 'status' => 1])
                ->assertStatus(422)->assertJsonValidationErrors('id');
        }
    }

    public function test_a_status_switch_still_works(): void
    {
        $department = $this->insertRow('departments', ['name' => 'Frozen', 'is_active' => true]);

        $this->actingAs($this->superAdmin())->postJson(route('warehouse.departments.status'), ['id' => $department, 'status' => 0])->assertOk();
        $this->assertFalse((bool) DB::table('departments')->where('id', $department)->value('is_active'));
    }

    public function test_approving_a_stock_request_that_is_not_awaiting_approval_changes_nothing(): void
    {
        // (The SQLite test schema only knows the original four statuses, so the
        // "awaiting_approval -> pending" path is checked on Postgres instead.)
        $admin = $this->superAdmin();
        $store = $this->insertRow('store_details', ['store_name' => 'Bissonnet']);
        foreach (['rejected', 'completed'] as $status) {
            $request = $this->insertRow('stock_requests', ['store_id' => $store, 'product_id' => 1, 'requested_quantity' => 5, 'status' => $status]);

            $this->actingAs($admin)->postJson(route('warehouse.stock-requests.approve', $request))
                ->assertStatus(422)->assertJson(['success' => false]);
            $this->assertSame($status, DB::table('stock_requests')->where('id', $request)->value('status'));
        }
    }

    public function test_the_ledger_export_includes_movements_of_deleted_products(): void
    {
        $product = $this->insertRow('products', ['product_name' => 'Kept product', 'store_id' => null, 'upc' => '111']);
        $this->insertRow('stock_transactions', ['product_id' => $product, 'warehouse_id' => 1, 'type' => 'purchase', 'quantity_change' => 5, 'running_balance' => 5, 'created_at' => now()]);
        $this->insertRow('stock_transactions', ['product_id' => 999999, 'warehouse_id' => 1, 'type' => 'adjustment', 'quantity_change' => -1, 'running_balance' => 0, 'created_at' => now()]);

        $csv = $this->actingAs($this->superAdmin())->get(route('warehouse.finance.ledger.export'))->assertOk()->streamedContent();

        $this->assertStringContainsString('Kept product', $csv);
        $this->assertStringContainsString('(deleted product)', $csv);
    }

    public function test_kitchen_menu_items_page_loads(): void
    {
        $this->insertRow('menu_items', ['name' => 'Jollof Rice']); // the error needs at least one item
        $this->actingAs($this->superAdmin())->get(route('kitchen.menu-items.index'))->assertOk();
    }
}
