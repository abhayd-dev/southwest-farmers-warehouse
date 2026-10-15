<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client tickets (10/15):
 * 1. Vendor Instructions typed on Create PO were not on the PO (the field was
 *    not saved at all).
 * 2. "Send Back to Vendor" reloaded the receive page and wiped Receive Qty;
 *    container returns now go with Process Receive.
 */
class PoVendorInstructionsAndContainerReturnTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();
    }

    public function test_vendor_instructions_are_saved_and_shown_on_the_po(): void
    {
        $this->actingAs($this->superAdmin());
        $product = $this->insertRow('products', ['product_name' => 'Plantain']);

        $this->post(route('warehouse.purchase-orders.store'), [
            'vendor_id' => $this->insertRow('vendors', ['name' => 'Fine Tropical']),
            'order_date' => now()->toDateString(),
            'vendor_notes' => "Deliver to dock 2\nCall before arriving",
            'items' => [['product_id' => $product, 'quantity' => 5, 'cost' => 28]],
        ]);

        $po = PurchaseOrder::latest('id')->firstOrFail();
        $this->assertSame("Deliver to dock 2\nCall before arriving", $po->vendor_notes);

        $this->get(route('warehouse.purchase-orders.show', $po))->assertOk()->assertSee('Vendor Instructions')->assertSee('Deliver to dock 2');
        $html = view('warehouse.purchase-orders.print', ['po' => $po->load('vendor', 'items.product')])->render();
        $this->assertStringContainsString('Vendor Instructions', $html);
        $this->assertStringContainsString('Call before arriving', $html);
    }

    public function test_process_receive_also_sends_containers_back(): void
    {
        $this->actingAs($this->superAdmin());
        $vendor = $this->insertRow('vendors', ['name' => 'Fine Tropical']);
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $vendor, 'po_number' => 'PO-T-1', 'order_date' => now()->toDateString(), 'status' => 'ordered', 'total_amount' => 280,
        ]));
        $item = $this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $this->insertRow('products', ['product_name' => 'Plantain']),
            'requested_quantity' => 10, 'unit_cost' => 28, 'total_cost' => 280,
        ]);
        DB::table('vendor_container_balances')->insert(['vendor_id' => $vendor, 'pallet_count' => 5, 'divider_count' => 2, 'created_at' => now(), 'updated_at' => now()]);

        $this->post(route('warehouse.purchase-orders.receive', $po), [
            'invoice_number' => 'INV-9988',
            'shipment_type' => 'truck',
            'items' => [$item => ['receive_qty' => 4]],
            'pallets_returned' => 3,
            'dividers_returned' => 1,
        ])->assertSessionHas('success', fn ($m) => str_contains($m, 'Containers sent back'));

        $this->assertEquals(4, DB::table('purchase_order_items')->where('id', $item)->value('received_quantity'));
        $this->assertEquals(2, DB::table('vendor_container_balances')->where('vendor_id', $vendor)->value('pallet_count'));
        $this->assertEquals(1, DB::table('vendor_container_balances')->where('vendor_id', $vendor)->value('divider_count'));
    }

    public function test_receive_page_has_no_separate_send_back_button(): void
    {
        $this->actingAs($this->superAdmin());
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $this->insertRow('vendors', ['name' => 'Fine Tropical']), 'po_number' => 'PO-T-2', 'order_date' => now()->toDateString(), 'status' => 'ordered',
        ]));

        $this->get(route('warehouse.receiving.show', $po))->assertOk()
            ->assertDontSee('Send Back to Vendor')
            ->assertSee('name="pallets_returned" form="receiveForm"', false);
    }
}
