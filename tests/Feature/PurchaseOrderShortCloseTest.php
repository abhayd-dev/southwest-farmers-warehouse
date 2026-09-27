<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client 9/27: ordered 100, 75 arrived, nothing more is coming. The order can
 * be completed short and the invoice is reduced to what was received (75),
 * keeping the original ordered quantity on record.
 */
class PurchaseOrderShortCloseTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();
    }

    /** A PO with two lines: 100 x $4 and 20 x $10 (invoice $600). */
    private function po(): array
    {
        $vendor = $this->insertRow('vendors', ['name' => 'Sysco Houston']);
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $vendor, 'po_number' => 'PO-SHORT-' . uniqid(), 'order_date' => now()->toDateString(),
            'status' => 'ordered', 'total_amount' => 600,
        ]));
        $lines = [];
        foreach ([[100, 4, 'Palm Oil'], [20, 10, 'Garri']] as [$qty, $cost, $name]) {
            $product = $this->insertRow('products', ['product_name' => $name, 'store_id' => null, 'cost_price' => $cost]);
            $lines[] = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
                'purchase_order_id' => $po->id, 'product_id' => $product, 'requested_quantity' => $qty,
                'unit_cost' => $cost, 'total_cost' => $qty * $cost,
            ]));
        }

        return [$po, $lines];
    }

    private function receiveForm(PurchaseOrder $po, array $items, string $shipment, bool $completeNow = false)
    {
        return $this->post(route('warehouse.purchase-orders.receive', $po), array_filter([
            'invoice_number' => 'INV-1', 'shipment_type' => $shipment, 'items' => $items, 'complete_now' => $completeNow ? '1' : null,
        ]));
    }

    public function test_container_with_nothing_more_coming_completes_and_invoices_only_what_arrived(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $this->actingAs($this->superAdmin());

        $this->receiveForm($po, [$oil->id => ['receive_qty' => 75], $garri->id => ['receive_qty' => 20]], 'container', true)
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'invoice now covers only what was received ($500.00)'));

        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertEquals(75, $oil->refresh()->requested_quantity);
        $this->assertEquals(500, (float) $po->total_amount); // 75 x 4 + 20 x 10
        $this->assertEquals([['item_id' => $oil->id, 'product' => 'Palm Oil', 'ordered' => 100, 'received' => 75, 'unit_cost' => 4]], $po->short_close_lines);
        $this->assertNotNull($po->short_closed_at);
    }

    public function test_container_without_the_checkbox_stays_open_and_the_invoice_is_unchanged(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $this->actingAs($this->superAdmin());

        $this->receiveForm($po, [$oil->id => ['receive_qty' => 75], $garri->id => ['receive_qty' => 20]], 'container');

        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $po->status);
        $this->assertEquals(600, (float) $po->total_amount);
        $this->assertNull($po->short_close_lines);
    }

    public function test_complete_order_on_a_partial_po_closes_it_short(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $admin = $this->superAdmin();
        $this->actingAs($admin)->receiveForm($po, [$oil->id => ['receive_qty' => 75], $garri->id => ['receive_qty' => 12]], 'container');
        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $po->refresh()->status);

        $this->actingAs($admin)->post(route('warehouse.purchase-orders.mark-completed', $po))
            ->assertSessionHas('success', 'Order completed. 2 short line(s) adjusted to the quantity received; invoice total is now $420.00.');

        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertEquals(420, (float) $po->total_amount); // 75 x 4 + 12 x 10
        $this->assertCount(2, $po->short_close_lines);
        $this->assertSame($admin->name, $po->short_closed_by);

        // Shown on the PO page.
        $this->actingAs($admin)->get(route('warehouse.purchase-orders.show', $po))->assertOk()
            ->assertSee('Completed short')->assertSee('Invoice reduced by $180.00 to $420.00', false);
    }

    public function test_a_truck_short_receipt_completes_and_syncs_the_invoice(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $this->actingAs($this->superAdmin());

        $this->receiveForm($po, [$oil->id => ['receive_qty' => 75], $garri->id => ['receive_qty' => 20]], 'truck');

        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertEquals(500, (float) $po->total_amount);
    }

    public function test_ordered_qty_can_be_edited_on_a_line_where_nothing_arrived(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $this->actingAs($this->superAdmin());

        // Garri: none arrived and none is coming -> Ordered Qty 0.
        $this->receiveForm($po, [$oil->id => ['receive_qty' => 100], $garri->id => ['receive_qty' => 0, 'ordered_qty' => 0]], 'container');

        $po->refresh();
        $this->assertEquals(0, $garri->refresh()->requested_quantity);
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertEquals(400, (float) $po->total_amount);
    }

    public function test_a_fully_received_order_records_no_short_lines(): void
    {
        [$po, [$oil, $garri]] = $this->po();
        $this->actingAs($this->superAdmin());

        $this->receiveForm($po, [$oil->id => ['receive_qty' => 100], $garri->id => ['receive_qty' => 20]], 'container', true);

        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertEquals(600, (float) $po->total_amount);
        $this->assertNull($po->short_close_lines);
    }
}
