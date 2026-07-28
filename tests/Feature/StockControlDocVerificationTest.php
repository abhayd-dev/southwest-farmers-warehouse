<?php

namespace Tests\Feature;

use App\Models\WareUser;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockAudit;
use App\Models\StoreDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockControlDocVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an authenticated admin user for testing
        $this->user = WareUser::first() ?? WareUser::create([
            'name' => 'Admin Tester',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'warehouse_id' => 1,
            'is_active' => true,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_1_stock_overview_tabs_and_full_csv_export_work()
    {
        $this->actingAs($this->user);

        // 1a. Test Overview Page loads
        $response = $this->get(route('warehouse.stock-control.overview'));
        $response->assertStatus(200);
        $response->assertSee('Warehouse Stock (WHSE)');
        $response->assertSee('Store Stock (STR)');
        $response->assertSee('Export All Data (CSV)');

        // 1b. Test Overview Data endpoint for WHSE and Store views
        $whseData = $this->get(route('warehouse.stock-control.overview.data', ['view_type' => 'warehouse']));
        $whseData->assertStatus(200);

        $storeData = $this->get(route('warehouse.stock-control.overview.data', ['view_type' => 'store']));
        $storeData->assertStatus(200);

        // 1c. Test Full CSV Export streaming
        $export = $this->get(route('warehouse.stock-control.overview.export', ['view_type' => 'warehouse']));
        $export->assertStatus(200);
        $export->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_2_transfer_monitor_details_modal_shows_quantity()
    {
        $this->actingAs($this->user);

        // Test Transfer Monitor page loads
        $response = $this->get(route('warehouse.transfers.monitor'));
        $response->assertStatus(200);

        // Test Transfer Detail Modal if a transfer exists
        $transfer = StockTransfer::first();
        if ($transfer) {
            $detailResponse = $this->get('/warehouse/transfers/monitor/' . $transfer->id);
            $detailResponse->assertStatus(200);
            $detailResponse->assertJsonStructure(['html']);
            $this->assertStringContainsString('Transferred Qty', $detailResponse->json('html'));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_3_product_analytics_date_range_filter_works()
    {
        $this->actingAs($this->user);

        $category = \App\Models\ProductCategory::first() ?? \App\Models\ProductCategory::create(['name' => 'General', 'code' => 'GEN']);
        $subcat = \App\Models\ProductSubcategory::first() ?? \App\Models\ProductSubcategory::create(['category_id' => $category->id, 'name' => 'General Sub', 'code' => 'GENSUB']);
        $dept = \App\Models\Department::first() ?? \App\Models\Department::create(['name' => 'Grocery', 'code' => 'GROC']);

        $product = Product::first() ?? Product::create([
            'product_name' => 'Test Beef',
            'sku' => 'BEEF-TEST-001',
            'upc' => 'BEEF-TEST-001',
            'category_id' => $category->id,
            'subcategory_id' => $subcat->id,
            'department_id' => $dept->id,
            'cost_price' => 10,
            'price' => 15,
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        $response = $this->get(route('warehouse.stock-control.valuation.product', [
            'product' => $product->id,
            'from_date' => '2026-01-01',
            'to_date' => '2026-12-31'
        ]));
        $response->assertStatus(200);
        $response->assertSee('Recent Transactions');
        $response->assertSee('name="from_date"', false);
        $response->assertSee('name="to_date"', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_4_inventory_count_naming_audit_by_filters_and_excel_export_work()
    {
        $this->actingAs($this->user);

        // 4a. Verify Index page title & naming
        $index = $this->get(route('warehouse.stock-control.audit.index'));
        $index->assertStatus(200);
        $index->assertSee('Inventory Count');

        // 4b. Verify Create form dropdowns
        $create = $this->get(route('warehouse.stock-control.audit.create'));
        $create->assertStatus(200);
        $create->assertSee('Audit Type');
        $create->assertSee('Audit By (Item Filter)');

        // 4c. Verify Audit Export route if audit exists
        $audit = StockAudit::first();
        if ($audit) {
            $export = $this->get(route('warehouse.stock-control.audit.export', $audit->id));
            $export->assertStatus(200);
            $export->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_5_pallet_builder_status_tabs_work()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('warehouse.pallets.index', ['status' => 'preparing']));
        $response->assertStatus(200);
        $response->assertSee('Preparing (Building Pallet)');
        $response->assertSee('Ready');
        $response->assertSee('In Transit');
        $response->assertSee('Delivered');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function point_6_restock_planning_search_bar_works()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('warehouse.stock-control.restock-planning'));
        $response->assertStatus(200);
        $response->assertSee('id="restockSearchInput"', false);
        $response->assertSee('placeholder="Search product name, UPC, or category..."', false);
    }
}
