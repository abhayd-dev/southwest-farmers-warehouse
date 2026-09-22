<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client feedback 9/21, Warehouse items 1-2: a shipment can arrive over or
 * under what was ordered, and the warehouse doesn't send back the excess or
 * wait on the shortfall. The Ordered Qty should be editable to reflect what
 * actually arrived, the invoice total should reflect that corrected
 * quantity, and a shipment that came up short (with nothing further coming)
 * should complete the order rather than leave it "partial" forever.
 */
class PurchaseOrderReceivingTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private int $vendorId;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();
        $this->vendorId = $this->insertRow('vendors', ['name' => 'Acme Foods']);
    }

    private function product(): Product
    {
        return Product::find($this->insertRow('products', ['product_name' => 'Bell Pepper', 'store_id' => null, 'cost_price' => 10]));
    }

    private function poWithItem(int $requestedQty, float $unitCost = 15): array
    {
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $this->vendorId, 'po_number' => 'PO-TEST-' . uniqid(),
            'order_date' => now()->toDateString(), 'status' => 'ordered', 'total_amount' => $requestedQty * $unitCost,
        ]));
        $product = $this->product();
        $item = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $product->id,
            'requested_quantity' => $requestedQty, 'unit_cost' => $unitCost, 'total_cost' => $requestedQty * $unitCost,
        ]));

        return [$po, $item, $product];
    }

    private function receive(PurchaseOrder $po, array $items, array $extra = []): PurchaseOrder
    {
        return app(\App\Services\PurchaseOrderService::class)->receiveItems(
            $po->id, $items, 'INV-' . uniqid(),
            $extra['duties'] ?? 0, $extra['shipping'] ?? 0, $extra['taxes'] ?? 0, $extra['transport'] ?? 0, $extra['demurrage'] ?? 0
        );
    }

    public function test_receiving_more_than_ordered_is_accepted_and_stock_reflects_the_full_amount(): void
    {
        [$po, $item, $product] = $this->poWithItem(100, 15);

        $result = $this->receive($po, [$item->id => ['receive_qty' => 125, 'ordered_qty' => 125]]);

        $item->refresh();
        $this->assertSame(125, $item->received_quantity);
        $this->assertSame(125, $item->requested_quantity, 'Ordered Qty is corrected to match what arrived');
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $result->status);
        $this->assertSame(125 * 15.0, (float) $result->total_amount, 'invoice total reflects the corrected quantity');

        $stock = ProductStock::where('product_id', $product->id)->where('warehouse_id', 1)->first();
        $this->assertSame(125, $stock->quantity);
    }

    public function test_receiving_less_than_ordered_with_ordered_qty_corrected_completes_the_order(): void
    {
        [$po, $item] = $this->poWithItem(100, 15);

        $result = $this->receive($po, [$item->id => ['receive_qty' => 75, 'ordered_qty' => 75]]);

        $item->refresh();
        $this->assertSame(75, $item->received_quantity);
        $this->assertSame(75, $item->requested_quantity);
        $this->assertSame(
            PurchaseOrder::STATUS_COMPLETED,
            $result->status,
            'client: "type of order will be considered Completed... warehouse will not be expecting the shorted amount"'
        );
        $this->assertSame(75 * 15.0, (float) $result->total_amount);
    }

    public function test_receiving_less_without_editing_ordered_qty_still_leaves_it_partial(): void
    {
        // Editing Ordered Qty is what signals "nothing more is coming"; leaving
        // it as-is means the warehouse is still expecting the remainder.
        [$po, $item] = $this->poWithItem(100, 15);

        $result = $this->receive($po, [$item->id => ['receive_qty' => 75]]);

        $item->refresh();
        $this->assertSame(75, $item->received_quantity);
        $this->assertSame(100, $item->requested_quantity, 'unchanged when no override is sent');
        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $result->status);
    }

    public function test_a_line_left_untouched_in_a_partial_receive_keeps_the_order_from_completing(): void
    {
        // Regression: this line used to `continue` before the completeness
        // check, so a PO with one item fully received and another left
        // completely untouched (0 in the form) could be wrongly marked
        // "completed".
        [$po, $itemA] = $this->poWithItem(50, 10);
        $productB = $this->product();
        $itemB = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $productB->id,
            'requested_quantity' => 20, 'unit_cost' => 5, 'total_cost' => 100,
        ]));

        $result = $this->receive($po, [
            $itemA->id => ['receive_qty' => 50],
            $itemB->id => ['receive_qty' => 0],
        ]);

        $this->assertSame(PurchaseOrder::STATUS_PARTIAL, $result->status);
        $this->assertSame(0, $itemB->refresh()->received_quantity);
    }

    public function test_a_line_left_untouched_does_not_trigger_a_shortage_email(): void
    {
        Mail::fake();
        [$po, $itemA] = $this->poWithItem(50, 10);
        $productB = $this->product();
        $itemB = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $productB->id,
            'requested_quantity' => 20, 'unit_cost' => 5, 'total_cost' => 100,
        ]));
        $this->insertRow('ware_permissions', ['name' => 'view_po', 'guard_name' => 'web']);
        $this->userWithPermissions(['manage_users']); // ensure at least one active ware user exists as a recipient candidate, harmless if unused

        $this->receive($po, [
            $itemA->id => ['receive_qty' => 50],
            $itemB->id => ['receive_qty' => 0],
        ]);

        Mail::assertNothingSent();
    }

    public function test_the_receive_endpoint_accepts_a_quantity_larger_than_what_was_pending(): void
    {
        // The old HTML max="{{ pending_quantity }}" attribute used to block
        // this client-side; the server never had a cap, and now neither does
        // the validated request.
        [$po, $item] = $this->poWithItem(10);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('warehouse.purchase-orders.receive', $po), [
            'invoice_number' => 'INV-1',
            'items' => [$item->id => ['receive_qty' => 999, 'ordered_qty' => 999]],
        ])->assertRedirect(route('warehouse.receiving.show', $po->id));

        $this->assertSame(999, $item->refresh()->received_quantity);
    }
}
