<?php

namespace Tests\Feature;

use App\Mail\OverReceiptApprovalRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\OverReceiptService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client PDF 9/24, Warehouse items 1-3:
 * 1. More received than ordered -> received anyway, order flagged and sent
 *    to the approver.
 * 2. Short off a container -> order stays open (In Transit).
 * 3. Short off a truck -> order closed.
 */
class ShipmentTypeAndOverReceiptTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();
    }

    private function po(int $ordered = 100, float $unitCost = 2): array
    {
        $vendorId = $this->insertRow('vendors', ['name' => 'Acme Foods']);
        $productId = $this->insertRow('products', ['product_name' => 'Lemon 60ct', 'store_id' => null, 'cost_price' => 2]);
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $vendorId, 'po_number' => 'PO-T-' . uniqid(), 'order_date' => now()->toDateString(),
            'status' => 'ordered', 'total_amount' => $ordered * $unitCost, 'approval_email' => 'approver@example.com',
            'over_receipt_status' => null, 'over_receipt_lines' => null, 'shipment_type' => null,
        ]));
        $item = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $productId,
            'requested_quantity' => $ordered, 'unit_cost' => $unitCost, 'total_cost' => $ordered * $unitCost,
        ]));

        return [$po, $item];
    }

    private function receive(PurchaseOrder $po, PurchaseOrderItem $item, int $qty, ?string $shipment): PurchaseOrder
    {
        $this->actingAs($this->superAdmin());

        return app(PurchaseOrderService::class)->receiveItems($po->id, [$item->id => ['receive_qty' => $qty]], 'INV-1', 0, 0, 0, 0, 0, null, $shipment)->fresh();
    }

    public function test_short_container_receipt_keeps_the_order_open_in_transit(): void
    {
        [$po, $item] = $this->po(100);
        $po = $this->receive($po, $item, 75, 'container');

        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $po->status); // shown as IN TRANSIT
        $this->assertSame('container', $po->shipment_type);
        $this->assertEquals(25, $item->fresh()->pending_quantity);
    }

    public function test_short_truck_receipt_closes_the_order(): void
    {
        [$po, $item] = $this->po(100);
        $po = $this->receive($po, $item, 75, 'truck');

        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->status);
        $this->assertSame('truck', $po->shipment_type);
    }

    public function test_over_receipt_is_received_flagged_and_sent_to_the_approver(): void
    {
        [$po, $item] = $this->po(100, 2);
        $po = $this->receive($po, $item, 125, 'truck');

        $this->assertEquals(125, $item->fresh()->received_quantity);
        $this->assertSame(PurchaseOrder::OVER_RECEIPT_PENDING, $po->over_receipt_status);
        $this->assertEquals([['item_id' => $item->id, 'product' => 'Lemon 60ct', 'ordered' => 100, 'received' => 125, 'unit_cost' => 2.0]], $po->over_receipt_lines);
        Mail::assertSent(OverReceiptApprovalRequest::class, fn ($m) => $m->hasTo('approver@example.com'));
    }

    public function test_approving_an_over_receipt_bills_the_received_quantity(): void
    {
        [$po, $item] = $this->po(100, 2);
        $po = $this->receive($po, $item, 125, 'truck');

        $this->assertTrue(app(OverReceiptService::class)->decide($po, 'approve', 'approver@example.com'));

        $po->refresh();
        $this->assertSame(PurchaseOrder::OVER_RECEIPT_APPROVED, $po->over_receipt_status);
        $this->assertEquals(125, $item->fresh()->requested_quantity);
        $this->assertEquals(250, $po->total_amount);
        $this->assertFalse(app(OverReceiptService::class)->decide($po, 'reject', 'x'), 'second decision is ignored');
    }

    public function test_rejecting_an_over_receipt_keeps_the_ordered_quantity_even_if_ordered_qty_was_edited(): void
    {
        [$po, $item] = $this->po(100, 2);
        $this->actingAs($this->superAdmin());
        app(PurchaseOrderService::class)->receiveItems($po->id, [$item->id => ['receive_qty' => 125, 'ordered_qty' => 125]], 'INV-1', 0, 0, 0, 0, 0, null, 'truck');

        app(OverReceiptService::class)->decide($po->fresh(), 'reject', 'approver@example.com');

        $this->assertEquals(100, $item->fresh()->requested_quantity);
        $this->assertEquals(200, $po->fresh()->total_amount);
        $this->assertEquals(125, $item->fresh()->received_quantity, 'stock stays received');
    }

    public function test_signed_email_link_approves_and_unsigned_is_refused(): void
    {
        [$po, $item] = $this->po(100, 2);
        $po = $this->receive($po, $item, 110, 'container');
        auth()->logout();

        $this->get(route('warehouse.purchase-orders.over-receipt.decide', ['purchaseOrder' => $po->id, 'decision' => 'approve']))
            ->assertForbidden();

        $this->get(app(OverReceiptService::class)->decisionUrl($po, 'approve'))
            ->assertOk()->assertSee('Over-receipt approved');
        $this->assertSame(PurchaseOrder::OVER_RECEIPT_APPROVED, $po->fresh()->over_receipt_status);
    }

    public function test_in_app_buttons_decide_and_the_pages_show_the_flag(): void
    {
        [$po, $item] = $this->po(100, 2);
        $po = $this->receive($po, $item, 110, 'truck');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('warehouse.purchase-orders.show', $po))->assertOk()->assertSee('Over-receipt: awaiting approval');
        $this->actingAs($admin)->get(route('warehouse.receiving.show', $po))->assertOk()->assertSee('Over-receipt: awaiting approval')->assertSee('Shipment Type');

        $this->actingAs($admin)->post(route('warehouse.purchase-orders.over-receipt', $po), ['decision' => 'reject'])
            ->assertSessionHas('success');
        $this->assertSame(PurchaseOrder::OVER_RECEIPT_REJECTED, $po->fresh()->over_receipt_status);
    }

    public function test_receive_form_requires_a_shipment_type(): void
    {
        [$po, $item] = $this->po(100);

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.receive', $po), [
            'invoice_number' => 'INV-1',
            'items' => [$item->id => ['receive_qty' => 10]],
        ])->assertSessionHasErrors('shipment_type');
    }

    public function test_decimal_quantities_can_be_received(): void
    {
        // QA: "Receiving orders still cannot accept decimal values" (e.g. by weight).
        [$po, $item] = $this->po(20, 2);
        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.receive', $po), [
            'invoice_number' => 'INV-1', 'shipment_type' => 'container',
            'items' => [$item->id => ['receive_qty' => '12.5']],
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame(12.5, $item->received_quantity);
        $this->assertSame(7.5, $item->pending_quantity);
        $this->assertEquals(12.5, \App\Models\ProductStock::where('product_id', $item->product_id)->value('quantity'));
        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $po->fresh()->status);
    }
}
