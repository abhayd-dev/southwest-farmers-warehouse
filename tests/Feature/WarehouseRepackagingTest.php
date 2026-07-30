<?php

namespace Tests\Feature;

use App\Models\WareUser;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseRepackagingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $bulkProduct;
    protected $targetProduct;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Warehouse::firstOrCreate(
            ['id' => 1],
            ['warehouse_name' => 'Central Warehouse', 'code' => 'WH01', 'is_active' => true]
        );

        $this->user = WareUser::create([
            'name' => 'Admin Tester',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'warehouse_id' => 1,
            'is_active' => true,
        ]);

        $category = ProductCategory::create(['name' => 'General', 'code' => 'GEN']);
        $subcat = ProductSubcategory::create(['category_id' => $category->id, 'name' => 'General Sub', 'code' => 'GENSUB']);
        $dept = Department::create(['name' => 'Grocery', 'code' => 'GROC']);

        // 1. Bulk Product (e.g. GHANA GARI 50LBS)
        $this->bulkProduct = Product::create([
            'product_name'   => 'GHANA GARI 50LBS',
            'sku'            => 'GARI-50LB',
            'upc'            => '8_584145',
            'category_id'    => $category->id,
            'subcategory_id' => $subcat->id,
            'department_id'  => $dept->id,
            'cost_price'     => 50.00,
            'price'          => 75.00,
            'unit'           => 'LBS',
            'is_active'      => true,
        ]);

        ProductStock::create([
            'product_id' => $this->bulkProduct->id,
            'warehouse_id' => 1,
            'quantity' => 100,
        ]);

        // 2. Repackaged Variant Product (e.g. GHANA GARI 20LBS)
        $this->targetProduct = Product::create([
            'product_name'   => 'GHANA GARI 20LBS',
            'sku'            => 'GARI-20LB',
            'upc'            => '8_007386',
            'category_id'    => $category->id,
            'subcategory_id' => $subcat->id,
            'department_id'  => $dept->id,
            'cost_price'     => 20.00,
            'price'          => 30.00,
            'unit'           => 'LBS',
            'is_active'      => true,
        ]);

        ProductStock::create([
            'product_id' => $this->targetProduct->id,
            'warehouse_id' => 1,
            'quantity' => 10,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_repackaging_page_loads_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('warehouse.stock-control.repackaging.index'));
        $response->assertStatus(200);
        $response->assertSee('Warehouse Repackaging');
        $response->assertSee('Product that is getting repackaged');
        $response->assertSee('Confirm Repackaging Transfer');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_associated_products_ajax_returns_json()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('warehouse.stock-control.repackaging.associated', $this->bulkProduct->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'base_name' => 'GHANA GARI'
        ]);

        $this->assertCount(1, $response->json('products'));
        $this->assertEquals('GHANA GARI 20LBS', $response->json('products.0.product_name'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_repackaging_transfer_deducts_bulk_stock_and_increases_target_stock()
    {
        $this->actingAs($this->user);

        $payload = [
            'source_product_id' => $this->bulkProduct->id,
            'source_quantity'   => 5,
            'remarks'           => 'repackaging transfer',
            'items'             => [
                [
                    'product_id' => $this->targetProduct->id,
                    'quantity'   => 12,
                ]
            ]
        ];

        $response = $this->post(route('warehouse.stock-control.repackaging.store'), $payload);
        $response->assertRedirect(route('warehouse.stock-control.repackaging.index'));
        $response->assertSessionHas('success');

        // Verify Bulk Stock Deducted (100 - 5 = 95)
        $bulkStock = ProductStock::where('product_id', $this->bulkProduct->id)->where('warehouse_id', 1)->first();
        $this->assertEquals(95, $bulkStock->quantity);

        // Verify Target Stock Added (10 + 12 = 22)
        $targetStock = ProductStock::where('product_id', $this->targetProduct->id)->where('warehouse_id', 1)->first();
        $this->assertEquals(22, $targetStock->quantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_repackaging_transfer_creates_stock_transactions_with_repackaging_transfer_remarks()
    {
        $this->actingAs($this->user);

        $payload = [
            'source_product_id' => $this->bulkProduct->id,
            'source_quantity'   => 2,
            'remarks'           => 'repackaging transfer',
            'items'             => [
                [
                    'product_id' => $this->targetProduct->id,
                    'quantity'   => 5,
                ]
            ]
        ];

        $this->post(route('warehouse.stock-control.repackaging.store'), $payload);

        // Check Outbound Transaction Log for Bulk Product
        $outTxn = StockTransaction::where('product_id', $this->bulkProduct->id)->latest()->first();
        $this->assertNotNull($outTxn);
        $this->assertEquals(-2, $outTxn->quantity_change);
        $this->assertEquals('repackaging transfer', $outTxn->remarks);

        // Check Inbound Transaction Log for Repackaged Product
        $inTxn = StockTransaction::where('product_id', $this->targetProduct->id)->latest()->first();
        $this->assertNotNull($inTxn);
        $this->assertEquals(5, $inTxn->quantity_change);
        $this->assertEquals('repackaging transfer', $inTxn->remarks);
    }
}
