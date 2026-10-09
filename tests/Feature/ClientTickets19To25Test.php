<?php

namespace Tests\Feature;

use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockTransaction;
use App\Models\Vendor;
use App\Models\VendorContainerBalance;
use App\Models\VendorContainerTransaction;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client tickets 19-25 (10/5-10/7) plus the "nearest valid values" decimal
 * quantity report: Order Summary fees, Correct Order on completed POs,
 * pallet/divider inventory per vendor, and PO headings without "#".
 */
class ClientTickets19To25Test extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private int $vendorId;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();
        $this->vendorId = $this->insertRow('vendors', ['name' => 'Cantus Produce']);
    }

    /** An ordered PO with one line of 50 x $4 (Plantain). */
    private function orderedPo(): array
    {
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $this->vendorId, 'po_number' => 'PO-20261007-' . random_int(1000, 9999),
            'order_date' => now()->toDateString(), 'status' => 'ordered', 'total_amount' => 200,
        ]));
        $product = $this->insertRow('products', ['product_name' => 'Plantain', 'store_id' => null, 'cost_price' => 4]);
        $item = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $product, 'requested_quantity' => 50,
            'unit_cost' => 4, 'total_cost' => 200,
        ]));

        return [$po, $item];
    }

    private function receive(PurchaseOrder $po, array $items, array $extra = [])
    {
        return $this->post(route('warehouse.purchase-orders.receive', $po), $extra + [
            'invoice_number' => 'INV-1', 'shipment_type' => 'truck', 'items' => $items,
        ]);
    }

    /** Received in full by truck: completed, 50 in stock in one batch. */
    private function completedPo(): array
    {
        [$po, $item] = $this->orderedPo();
        $this->actingAs($this->superAdmin());
        $this->receive($po, [$item->id => ['receive_qty' => 50]])->assertSessionHasNoErrors();
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $po->refresh()->status);

        return [$po, $item->refresh()];
    }

    private function stockOf(PurchaseOrderItem $item): float
    {
        return (float) ProductStock::where('product_id', $item->product_id)->where('warehouse_id', 1)->value('quantity');
    }

    private function batchOf(PurchaseOrder $po, PurchaseOrderItem $item): ProductBatch
    {
        $id = StockTransaction::where('type', 'purchase_in')->where('reference_id', (string) $po->id)
            ->where('product_id', $item->product_id)->value('product_batch_id');

        return ProductBatch::findOrFail($id);
    }

    private function correct(PurchaseOrder $po, PurchaseOrderItem $item, float $received, float $ordered, array $extra = [])
    {
        return $this->post(route('warehouse.purchase-orders.correct', $po), $extra + [
            'reason' => 'Miscounted on the dock',
            'items' => [$item->id => ['requested_quantity' => $ordered, 'received_quantity' => $received, 'unit_cost' => 4]],
        ]);
    }

    // ---- Ticket 19: duties/shipping/taxes make up the Total Amount ----

    public function test_order_summary_lists_fees_and_includes_them_in_the_total(): void
    {
        [$po] = $this->completedPo();
        $po->update(['duties' => 10, 'shipping_cost' => 5, 'taxes' => 2.5]);

        $this->get(route('warehouse.purchase-orders.show', $po))->assertOk()
            ->assertSeeInOrder(['Subtotal (items):', '$200.00', 'Duties:', '$10.00', 'Shipping Cost:', '$5.00', 'Taxes:', '$2.50', 'Total Amount:', '$217.50'], false);
    }

    public function test_order_summary_without_fees_is_unchanged(): void
    {
        [$po] = $this->completedPo();

        $this->get(route('warehouse.purchase-orders.show', $po))->assertOk()
            ->assertDontSee('Subtotal (items):')->assertSeeInOrder(['Total Amount:', '$200.00'], false);
    }

    // ---- Ticket 22: Correct Order (admin only) ----

    public function test_lowering_received_qty_takes_it_out_of_stock_and_this_orders_batch(): void
    {
        [$po, $item] = $this->completedPo();
        $this->assertEquals(50, $this->stockOf($item));

        $this->correct($po, $item, 45, 45)->assertRedirect(route('warehouse.purchase-orders.show', $po));

        $this->assertEquals(45, $item->refresh()->received_quantity);
        $this->assertEquals(45, $this->stockOf($item));
        $this->assertEquals(45, (float) $this->batchOf($po, $item)->quantity);
        $this->assertEquals(180, (float) $po->refresh()->total_amount); // 45 x $4
        $this->assertDatabaseHas('stock_transactions', ['type' => 'adjustment', 'reference_id' => (string) $po->id, 'quantity_change' => -5]);
    }

    public function test_raising_received_qty_adds_to_stock_and_this_orders_batch(): void
    {
        [$po, $item] = $this->completedPo();

        $this->correct($po, $item, 53, 53)->assertSessionHasNoErrors();

        $this->assertEquals(53, $this->stockOf($item));
        $this->assertEquals(53, (float) $this->batchOf($po, $item)->quantity);
    }

    public function test_lowering_is_refused_when_that_stock_already_went_to_a_store(): void
    {
        [$po, $item] = $this->completedPo();
        // 40 of the 50 dispatched to a store (what dispatchToStore does).
        $this->batchOf($po, $item)->decrement('quantity', 40);
        ProductStock::where('product_id', $item->product_id)->decrement('quantity', 40);

        $this->correct($po, $item, 30, 30)
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'only 10 of what this order brought in is still in the warehouse'));

        $this->assertEquals(50, $item->refresh()->received_quantity);
        $this->assertEquals(10, $this->stockOf($item));
        $this->assertEquals(10, (float) $this->batchOf($po, $item)->quantity);
    }

    public function test_only_admins_can_correct_and_only_completed_orders(): void
    {
        [$po] = $this->completedPo();

        $receiver = $this->userWithPermissions(['view_po', 'receive_po']);
        $this->actingAs($receiver)->get(route('warehouse.purchase-orders.correct-form', $po))->assertForbidden();
        $this->actingAs($receiver)->post(route('warehouse.purchase-orders.correct', $po), [])->assertForbidden();
        $this->actingAs($receiver)->get(route('warehouse.purchase-orders.show', $po))->assertDontSee('Correct Order');

        $admin = $this->superAdmin();
        $this->actingAs($admin)->get(route('warehouse.purchase-orders.show', $po))->assertSee('Correct Order');
        $this->actingAs($admin)->get(route('warehouse.purchase-orders.correct-form', $po))->assertOk()->assertSee('Save Correction');

        [$open] = $this->orderedPo();
        $this->actingAs($admin)->get(route('warehouse.purchase-orders.correct-form', $open))->assertForbidden();
    }

    // ---- Ticket 23: pallets and dividers per vendor ----

    public function test_pallets_and_dividers_received_returned_and_reconciled(): void
    {
        [$po, $item] = $this->orderedPo();
        $this->actingAs($this->superAdmin());
        $vendor = Vendor::find($this->vendorId);

        // Viewing the Receive page must not write anything.
        $this->get(route('warehouse.receiving.show', $po))->assertOk()
            ->assertSee('name="pallets_received"', false)->assertSee('name="dividers_received"', false)
            ->assertSee('Pallets &amp; Dividers to Cantus Produce', false)->assertSee('Send Back to Vendor');
        $this->assertSame(0, VendorContainerBalance::count());

        $this->receive($po, [$item->id => ['receive_qty' => 50]], ['pallets_received' => 5, 'dividers_received' => 3])
            ->assertSessionHasNoErrors();
        $balance = VendorContainerBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertEquals([5, 3], [$balance->pallet_count, $balance->divider_count]);

        $this->post(route('warehouse.receiving.return-containers', $po), ['pallets_returned' => 2, 'dividers_returned' => 0])
            ->assertSessionHas('success');
        $this->assertEquals(3, $balance->refresh()->pallet_count);

        $this->post(route('warehouse.receiving.return-containers', $po), ['pallets_returned' => 10])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Only 3 pallet(s) on hand'));
        $this->assertEquals(3, $balance->refresh()->pallet_count);

        $this->post(route('warehouse.vendor-containers.reconcile', $vendor), ['pallet_count' => 7, 'divider_count' => 1, 'reason' => 'Yard count'])
            ->assertSessionHas('success');
        $this->assertEquals([7, 1], [$balance->refresh()->pallet_count, $balance->divider_count]);

        $this->assertSame(
            ['received', 'received', 'returned', 'adjustment', 'adjustment'],
            VendorContainerTransaction::orderBy('id')->pluck('direction')->all()
        );
        $this->get(route('warehouse.vendor-containers.index'))->assertOk()->assertSee('Cantus Produce')->assertSee('7.00');
    }

    // ---- Ticket 25: no "#" in PO headings ----

    public function test_po_headings_show_the_number_without_a_hash(): void
    {
        [$po] = $this->completedPo();

        $this->get(route('warehouse.purchase-orders.show', $po))->assertOk()
            ->assertSee($po->po_number)->assertDontSee('#' . $po->po_number);
        $this->get(route('warehouse.receiving.show', $po))->assertOk()
            ->assertSee('Receive Order: ' . $po->po_number)->assertDontSee('#' . $po->po_number);
    }

    // ---- Extra: "Please enter a valid value ... 37037 and 37038" ----

    public function test_po_quantity_accepts_decimals_in_the_form_and_on_save(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->get(route('warehouse.purchase-orders.create'))->assertOk()
            ->assertSee('qty-input text-center" min="0.01" step="0.01"', false);

        $product = $this->insertRow('products', ['product_name' => 'Whiting Fish', 'store_id' => null, 'cost_price' => 1.05]);
        $this->actingAs($admin)->post(route('warehouse.purchase-orders.store'), [
            'vendor_id' => $this->vendorId, 'order_date' => now()->toDateString(),
            'items' => [['product_id' => $product, 'quantity' => 37037.28, 'cost' => 1.05]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(37037.28, PurchaseOrderItem::where('product_id', $product)->value('requested_quantity'));
    }
}
