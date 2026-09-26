<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client PDF 9/24, item 4: a view of open POs with how long each has been
 * open; client 9/11 list, item 8: times shown in Central, not UTC.
 */
class OpenPurchaseOrdersViewTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private function po(string $number, string $status, string $createdAt): void
    {
        $this->withoutForeignKeys();
        $this->insertRow('purchase_orders', [
            'po_number' => $number, 'status' => $status, 'approval_status' => 'approved',
            'vendor_id' => $this->insertRow('vendors', ['name' => 'V']), 'order_date' => '2026-09-01',
            'total_amount' => 10, 'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);
    }

    public function test_open_filter_lists_only_open_orders_with_their_age(): void
    {
        Carbon::setTestNow('2026-09-26 17:00:00'); // UTC
        $this->po('PO-OPEN-OLD', PurchaseOrder::STATUS_ORDERED, '2026-09-16 15:00:00');
        $this->po('PO-IN-TRANSIT', PurchaseOrder::STATUS_PARTIAL, '2026-09-26 14:30:00');
        $this->po('PO-DONE', PurchaseOrder::STATUS_COMPLETED, '2026-09-20 12:00:00');
        $this->po('PO-CANCELLED', PurchaseOrder::STATUS_CANCELLED, '2026-09-20 12:00:00');

        $json = $this->actingAs($this->superAdmin())
            ->getJson(route('warehouse.purchase-orders.index', ['status' => 'open', 'draw' => 1, 'start' => 0, 'length' => 50]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');

        $rows = collect($json)->keyBy('po_number');
        $this->assertEqualsCanonicalizing(['PO-OPEN-OLD', 'PO-IN-TRANSIT'], $rows->keys()->all());
        $this->assertStringContainsString('Open 10d 2h', $rows['PO-OPEN-OLD']['open_since']);
        $this->assertStringContainsString('text-warning', $rows['PO-OPEN-OLD']['open_since'], '7-13 days is amber');
        $this->assertStringContainsString('Open 2h 30m', $rows['PO-IN-TRANSIT']['open_since']);
        // 14:30 UTC on 9/26 is 09:30 AM Central (CDT)
        $this->assertStringContainsString('Sep 26, 2026 09:30 AM', $rows['PO-IN-TRANSIT']['open_since']);
    }

    public function test_in_transit_filter(): void
    {
        $this->po('PO-A', PurchaseOrder::STATUS_PARTIAL, '2026-09-20 12:00:00');
        $this->po('PO-B', PurchaseOrder::STATUS_ORDERED, '2026-09-20 12:00:00');

        $json = $this->actingAs($this->superAdmin())
            ->getJson(route('warehouse.purchase-orders.index', ['status' => 'in_transit', 'draw' => 1, 'start' => 0, 'length' => 50]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->json('data');

        $this->assertSame(['PO-A'], collect($json)->pluck('po_number')->all());
    }

    public function test_display_time_converts_utc_to_central(): void
    {
        $this->assertSame('11:23 AM', Carbon::parse('2026-09-22 16:23:05', 'UTC')->displayTime()->format('h:i A'));
        $this->assertSame('06:00 AM', Carbon::parse('2026-12-01 12:00:00', 'UTC')->displayTime()->format('h:i A'), 'CST in winter');
    }
}
