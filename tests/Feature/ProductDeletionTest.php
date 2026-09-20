<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private const REFERENCING_TABLES = [
        'pallet_items', 'sale_return_items', 'sale_items', 'stock_transfers',
        'stock_audit_items', 'purchase_order_items', 'store_purchase_order_items',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    private function product(?int $storeId = null): Product
    {
        return Product::find($this->insertRow('products', [
            'product_name' => 'Deletable ' . uniqid(),
            'store_id' => $storeId,
        ]));
    }

    private function referenceRowsFor(Product $product): void
    {
        foreach (self::REFERENCING_TABLES as $table) {
            $this->insertRow($table, ['product_id' => $product->id]);
        }
    }

    private function assertHasNoReferences(Product $product): void
    {
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        foreach (self::REFERENCING_TABLES as $table) {
            $this->assertDatabaseMissing($table, ['product_id' => $product->id]);
        }
    }

    public function test_deleting_a_product_removes_it_and_every_referencing_row(): void
    {
        $product = $this->product();
        $this->referenceRowsFor($product);
        foreach (self::REFERENCING_TABLES as $table) {
            $this->assertDatabaseHas($table, ['product_id' => $product->id]);
        }

        $this->actingAs($this->superAdmin())
            ->delete(route('warehouse.products.destroy', $product))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertHasNoReferences($product);
    }

    public function test_bulk_delete_only_removes_the_selected_warehouse_products(): void
    {
        $doomed = $this->product();
        $kept = $this->product();
        $storeProduct = $this->product(storeId: 7);
        $this->referenceRowsFor($doomed);
        $this->referenceRowsFor($kept);

        $this->actingAs($this->superAdmin())
            ->delete(route('warehouse.products.destroy-bulk'), ['ids' => [$doomed->id, $storeProduct->id]])
            ->assertSessionHas('success');

        $this->assertHasNoReferences($doomed);
        $this->assertDatabaseHas('products', ['id' => $kept->id]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $kept->id]);
        $this->assertDatabaseHas('products', ['id' => $storeProduct->id]);
    }

    public function test_delete_all_removes_warehouse_products_but_never_store_products(): void
    {
        $a = $this->product();
        $b = $this->product();
        $storeProduct = $this->product(storeId: 7);
        $this->referenceRowsFor($a);

        $this->actingAs($this->superAdmin())
            ->delete(route('warehouse.products.destroy-all'))
            ->assertSessionHas('success');

        $this->assertHasNoReferences($a);
        $this->assertDatabaseMissing('products', ['id' => $b->id]);
        $this->assertDatabaseHas('products', ['id' => $storeProduct->id]);
    }

    public function test_a_store_owned_product_cannot_be_deleted_through_the_warehouse_route(): void
    {
        $storeProduct = $this->product(storeId: 7);

        $this->actingAs($this->superAdmin())
            ->delete(route('warehouse.products.destroy', $storeProduct))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $storeProduct->id]);
    }

    public function test_users_without_delete_permission_are_forbidden(): void
    {
        $product = $this->product();
        $viewer = $this->userWithPermissions(['view_products']);

        $this->actingAs($viewer)->delete(route('warehouse.products.destroy', $product))->assertForbidden();
        $this->actingAs($viewer)->delete(route('warehouse.products.destroy-all'))->assertForbidden();
        $this->actingAs($viewer)->delete(route('warehouse.products.destroy-bulk'), ['ids' => [$product->id]])->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_a_user_with_only_delete_products_permission_can_delete(): void
    {
        $product = $this->product();

        $this->actingAs($this->userWithPermissions(['delete_products']))
            ->delete(route('warehouse.products.destroy', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
