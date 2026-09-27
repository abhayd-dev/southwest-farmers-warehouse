<?php

namespace Tests\Feature;

use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/** Recall Stock Management: Date From / To on My Requests and Store Requests. */
class RecallDateFilterTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    private function recall(int $initiatedBy, string $createdAtUtc): int
    {
        return $this->insertRow('recall_requests', [
            'store_id' => 1, 'product_id' => 1, 'requested_quantity' => 1, 'reason' => 'near_expiry',
            'initiated_by' => $initiatedBy, 'status' => 'pending_store_approval',
            'created_at' => $createdAtUtc, 'updated_at' => $createdAtUtc,
        ]);
    }

    private function idsFrom(string $route, array $filters): array
    {
        return collect($this->getJson(route($route, $filters + ['draw' => 1, 'start' => 0, 'length' => 50]))
            ->assertOk()->json('data'))->pluck('id')->sort()->values()->all();
    }

    public function test_my_requests_date_range_uses_the_chicago_day(): void
    {
        $admin = $this->superAdmin();
        $sept27 = $this->recall($admin->id, '2026-09-27 15:00:00');
        $eveningSept26 = $this->recall($admin->id, '2026-09-27 02:00:00'); // 9pm Sept 26 in Chicago
        $july16 = $this->recall($admin->id, '2026-07-16 15:00:00');
        $this->actingAs($admin);

        $this->assertEquals([$sept27], $this->idsFrom('warehouse.stock-control.recall.my-requests', ['date_from' => '2026-09-27', 'date_to' => '2026-09-27']));
        $this->assertEquals([$eveningSept26], $this->idsFrom('warehouse.stock-control.recall.my-requests', ['date_from' => '2026-09-26', 'date_to' => '2026-09-26']));
        $this->assertEquals(collect([$eveningSept26, $sept27])->sort()->values()->all(), $this->idsFrom('warehouse.stock-control.recall.my-requests', ['date_from' => '2026-09-01']));
        $this->assertEquals([$july16], $this->idsFrom('warehouse.stock-control.recall.my-requests', ['date_to' => '2026-08-01']));
        $this->assertCount(3, $this->idsFrom('warehouse.stock-control.recall.my-requests', ['date_from' => 'not-a-date']));
    }

    public function test_store_requests_date_range_filters_too(): void
    {
        $admin = $this->superAdmin();
        $recent = $this->recall($admin->id + 1000, '2026-09-27 15:00:00');
        $this->recall($admin->id + 1000, '2026-07-16 15:00:00');
        $this->actingAs($admin);

        $this->assertEquals([$recent], $this->idsFrom('warehouse.stock-control.recall.store-requests', ['date_from' => '2026-09-01', 'date_to' => '2026-09-30']));
    }
}
