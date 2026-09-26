<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * "Is Stackable?" / "Is Fragile?" on the product form: switching one off and
 * saving must store it as off (an unchecked checkbox isn't submitted at all).
 */
class ProductSwitchesTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    private function product(array $values = []): Product
    {
        return Product::find($this->insertRow('products', $values + [
            'product_name' => 'Switch test ' . uniqid(),
            'store_id' => null,
            'barcode' => 'BC' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /** Everything the edit form requires, without either switch. */
    private function formFields(): array
    {
        return [
            'department_id' => $this->insertRow('departments'),
            'category_id' => 1,
            'subcategory_id' => 1,
            'product_name' => 'Edited product',
            'unit' => 'each',
            'price' => 960,
            'warehouse_markup_percentage' => 0,
            'store_markup_percentage' => 29,
            'cost_price' => 500,
            'store_retail_price' => 662,
            'upc' => '12345',
            'barcode' => 'BC' . uniqid(),
            'units_per_carton' => 1,
        ];
    }

    public function test_switching_both_off_saves_them_as_off(): void
    {
        $product = $this->product(['is_stackable' => true, 'is_fragile' => true]);

        // What the browser sends with both switches off: only the hidden 0s.
        $this->actingAs($this->superAdmin())
            ->put(route('warehouse.products.update', $product), $this->formFields() + ['is_stackable' => '0', 'is_fragile' => '0'])
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertFalse($product->is_stackable);
        $this->assertFalse($product->is_fragile);
    }

    public function test_switches_left_out_of_the_request_still_save_as_off(): void
    {
        $product = $this->product(['is_stackable' => true, 'is_fragile' => true]);

        // A page opened before this fix has no hidden fields.
        $this->actingAs($this->superAdmin())
            ->put(route('warehouse.products.update', $product), $this->formFields())
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertFalse($product->is_stackable);
        $this->assertFalse($product->is_fragile);
    }

    public function test_switching_both_on_saves_them_as_on(): void
    {
        $product = $this->product(['is_stackable' => false, 'is_fragile' => false]);

        // Hidden 0 then checkbox 1 -- PHP keeps the last value.
        $this->actingAs($this->superAdmin())
            ->put(route('warehouse.products.update', $product), $this->formFields() + ['is_stackable' => '1', 'is_fragile' => '1'])
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertTrue($product->is_stackable);
        $this->assertTrue($product->is_fragile);
    }

    public function test_the_edit_form_shows_the_saved_state_and_sends_a_hidden_off_value(): void
    {
        $product = $this->product(['is_stackable' => false, 'is_fragile' => true]);

        $html = $this->actingAs($this->superAdmin())
            ->get(route('warehouse.products.edit', $product))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<input type="hidden" name="is_stackable" value="0">\s*<input[^>]*name="is_stackable"(?![^>]*checked)[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="is_fragile" value="0">\s*<input[^>]*name="is_fragile"[^>]*checked[^>]*>/', $html);
    }
}
