<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\WareUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class ProductDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_product_can_be_deleted_with_all_referencing_records(): void
    {
        // 1. Find or create an admin user and ensure Super Admin role
        $user = WareUser::where('is_active', true)->first();
        if (!$user) {
            $user = WareUser::factory()->create(['is_active' => true]);
        }
        $role = \App\Models\WareRole::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        // Ensure warehouse details exist
        $warehouseId = DB::table('ware_details')->value('id');
        if (!$warehouseId) {
            $warehouseId = DB::table('ware_details')->insertGetId([
                'ware_name' => 'Main Warehouse',
                'ware_code' => 'MAINWH',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure store users exist
        $storeUserId = DB::table('store_users')->value('id');
        if (!$storeUserId) {
            $storeUserId = DB::table('store_users')->insertGetId([
                'name' => 'Store Admin',
                'email' => 'storeadmin-' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure store details exist
        $storeId = DB::table('store_details')->value('id');
        if (!$storeId) {
            $storeId = DB::table('store_details')->insertGetId([
                'warehouse_id' => $warehouseId,
                'store_name' => 'Test Store',
                'store_code' => 'TESTST-' . uniqid(),
                'email' => 'store-' . uniqid() . '@example.com',
                'phone' => '1234567890',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure category and subcategory exist
        $categoryId = DB::table('product_categories')->value('id');
        if (!$categoryId) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'name' => 'Test Cat',
                'code' => 'TEST-CAT-' . uniqid(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $subcategoryId = DB::table('product_subcategories')->where('category_id', $categoryId)->value('id');
        if (!$subcategoryId) {
            $subcategoryId = DB::table('product_subcategories')->insertGetId([
                'category_id' => $categoryId,
                'name' => 'Test Subcat',
                'code' => 'TEST-SUBCAT-' . uniqid(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $departmentId = DB::table('departments')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'name' => 'Test Dept',
                'code' => 'TESTDEPT',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Create a test product
        $product = Product::create([
            'ware_user_id' => $user->id,
            'product_name' => 'Test Deletable Product',
            'sku' => 'TEST-DEL-' . uniqid(),
            'barcode' => 'TEST-DEL-' . uniqid(),
            'upc' => '123456789012',
            'unit' => 'pcs',
            'price' => 10.00,
            'cost_price' => 8.00,
            'warehouse_markup_percentage' => 0,
            'store_markup_percentage' => 0,
            'store_retail_price' => 12.00,
            'units_per_carton' => 1,
            'department_id' => $departmentId,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
        ]);

        // 3. Create parent records for relationships
        // Sale
        $saleId = DB::table('sales')->insertGetId([
            'store_id' => $storeId,
            'customer_id' => null,
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'subtotal' => 10.00,
            'gst_amount' => 0.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 10.00,
            'payment_method' => 'cash',
            'created_by' => $storeUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sale Return
        $saleReturnId = DB::table('sale_returns')->insertGetId([
            'store_id' => $storeId,
            'sale_id' => $saleId,
            'return_no' => 'RET-TEST-' . uniqid(),
            'total_refund' => 10.00,
            'created_by' => $storeUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Transfer
        $transferId = DB::table('stock_transfers')->insertGetId([
            'transfer_number' => 'TRF-TEST-' . uniqid(),
            'from_store_id' => $storeId,
            'to_store_id' => $storeId,
            'product_id' => $product->id,
            'quantity_sent' => 5,
            'status' => 'pending',
            'created_by' => $storeUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pallet
        $palletId = DB::table('pallets')->insertGetId([
            'transfer_id' => $transferId,
            'pallet_number' => 'PAL-TEST-' . uniqid(),
            'department_id' => $departmentId,
            'total_weight' => 10.00,
            'max_weight' => 2200.00,
            'status' => 'preparing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Audit
        $auditId = DB::table('stock_audits')->insertGetId([
            'audit_number' => 'AUD-TEST-' . uniqid(),
            'warehouse_id' => $warehouseId,
            'status' => 'draft',
            'initiated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vendor for PO
        $vendorId = DB::table('vendors')->value('id');
        if (!$vendorId) {
            $vendorId = DB::table('vendors')->insertGetId([
                'name' => 'Test Vendor',
                'contact_name' => 'Test Contact',
                'email' => 'vendor@example.com',
                'phone' => '1234567890',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Purchase Order
        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-' . uniqid(),
            'vendor_id' => $vendorId,
            'warehouse_id' => $warehouseId,
            'order_date' => now(),
            'total_amount' => 10.00,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Store PO
        $storePoId = DB::table('store_purchase_orders')->insertGetId([
            'po_number' => 'SPO-TEST-' . uniqid(),
            'store_id' => $storeId,
            'request_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Insert referencing records in the tables we handle
        DB::table('sale_items')->insert([
            'sale_id' => $saleId,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
            'total' => 10.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pallet_items')->insert([
            'pallet_id' => $palletId,
            'product_id' => $product->id,
            'quantity' => 5,
            'weight_per_unit' => 2.00,
            'total_weight' => 10.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sale_return_items')->insert([
            'sale_return_id' => $saleReturnId,
            'product_id' => $product->id,
            'quantity' => 1,
            'refund_price' => 10.00,
            'condition' => 'good',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_audit_items')->insert([
            'stock_audit_id' => $auditId,
            'product_id' => $product->id,
            'system_qty' => 10.00,
            'physical_qty' => 10.00,
            'variance_qty' => 0.00,
            'cost_price' => 8.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'product_id' => $product->id,
            'requested_quantity' => 10,
            'received_quantity' => 0,
            'unit_cost' => 8.00,
            'tax_percent' => 0.00,
            'total_cost' => 80.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('store_purchase_order_items')->insert([
            'store_po_id' => $storePoId,
            'product_id' => $product->id,
            'requested_qty' => 10,
            'dispatched_qty' => 0,
            'pending_qty' => 10,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Assert they exist in the database
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('pallet_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('sale_return_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('stock_transfers', ['product_id' => $product->id]);
        $this->assertDatabaseHas('stock_audit_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('purchase_order_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('store_purchase_order_items', ['product_id' => $product->id]);

        // 6. Act as user and hit delete route
        $response = $this->actingAs($user, 'warehouse')
            ->delete(route('warehouse.products.destroy', $product));

        // 7. Assert redirect back/session success
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // 8. Assert that product and references are deleted
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('sale_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('pallet_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('sale_return_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('stock_transfers', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('stock_audit_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('purchase_order_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('store_purchase_order_items', ['product_id' => $product->id]);
    }
}
