<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/** Market Pricing filters (search itself uses ilike, so it is checked on Postgres in the browser). */
class MarketPriceFiltersTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private int $market;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
        $this->market = $this->insertRow('markets', ['name' => 'testing', 'is_active' => true]);
    }

    private function productWithPrice(string $name, string $updatedAtUtc): void
    {
        $id = $this->insertRow('products', ['product_name' => $name, 'store_id' => null, 'is_active' => true, 'barcode' => uniqid('B')]);
        $this->insertRow('product_market_prices', [
            'product_id' => $id, 'market_id' => $this->market, 'cost_price' => 1, 'sale_price' => 2,
            'created_at' => $updatedAtUtc, 'updated_at' => $updatedAtUtc,
        ]);
    }

    private function namesFor(array $query): array
    {
        return $this->actingAs($this->superAdmin())
            ->get(route('warehouse.market-prices.index', ['market_id' => $this->market] + $query))
            ->assertOk()
            ->viewData('products')->pluck('product_name')->all();
    }

    public function test_last_updated_date_is_the_chicago_day_not_the_utc_day(): void
    {
        // 03:00 UTC on the 27th is 10pm on the 26th in Chicago.
        $this->productWithPrice('Evening update', '2026-09-27 03:00:00');
        $this->productWithPrice('Midday update', '2026-09-26 17:00:00');
        $this->productWithPrice('Old update', '2026-09-20 17:00:00');

        $this->assertSame(['Evening update', 'Midday update'], $this->namesFor(['last_updated' => '2026-09-26']));
        $this->assertSame([], $this->namesFor(['last_updated' => '2026-09-27']));
    }

    public function test_a_us_style_date_works_and_a_nonsense_date_is_ignored_with_a_warning(): void
    {
        $this->productWithPrice('Midday update', '2026-09-26 17:00:00');
        $this->productWithPrice('Old update', '2026-09-20 17:00:00');

        $this->assertSame(['Midday update'], $this->namesFor(['last_updated' => '09/26/2026']));

        $response = $this->actingAs($this->superAdmin())
            ->get(route('warehouse.market-prices.index', ['market_id' => $this->market, 'last_updated' => '26/09/2026']));
        $response->assertOk()->assertSessionHas('warning');
        $this->assertCount(2, $response->viewData('products'));
    }

    public function test_results_come_in_a_fixed_order_so_pages_do_not_repeat(): void
    {
        foreach (['Zeta', 'Alpha', 'Mango'] as $name) {
            $this->productWithPrice($name, '2026-09-26 17:00:00');
        }

        $this->assertSame(['Alpha', 'Mango', 'Zeta'], $this->namesFor([]));
    }
}
