<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Warehouse PDF 9/22, item 1: when receiving an order the user can attach a
 * picture (or PDF) of the vendor invoice to it.
 */
class ReceivingInvoiceUploadTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private PurchaseOrder $po;
    private PurchaseOrderItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('r2');
        $this->withoutForeignKeys();

        $vendorId = $this->insertRow('vendors', ['name' => 'Acme Foods']);
        $productId = $this->insertRow('products', ['product_name' => 'Roma Tomato', 'store_id' => null, 'cost_price' => 10]);
        $this->po = PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $vendorId, 'po_number' => 'PO-TEST-1', 'order_date' => now()->toDateString(),
            'status' => 'ordered', 'total_amount' => 150,
        ]));
        $this->item = PurchaseOrderItem::find($this->insertRow('purchase_order_items', [
            'purchase_order_id' => $this->po->id, 'product_id' => $productId,
            'requested_quantity' => 10, 'unit_cost' => 15, 'total_cost' => 150,
        ]));
    }

    public function test_invoice_photo_can_be_attached_while_receiving(): void
    {
        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.receive', $this->po), [
            'invoice_number' => 'INV-1026343',
            'invoice_document' => UploadedFile::fake()->image('invoice.jpg', 800, 1100),
            'items' => [$this->item->id => ['receive_qty' => 10]],
        ])->assertSessionHasNoErrors();

        $po = $this->po->fresh();
        $this->assertNotNull($po->invoice_document);
        Storage::disk('r2')->assertExists($po->invoice_document);
        $this->assertTrue($po->is_invoice_document_image);
        $this->assertStringContainsString($po->invoice_document, $po->invoice_document_url);
    }

    public function test_invoice_can_be_attached_or_replaced_after_receiving(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('warehouse.receiving.upload-invoice', $this->po), [
            'invoice_document' => UploadedFile::fake()->create('invoice.pdf', 300, 'application/pdf'),
        ])->assertSessionHas('success');
        $first = $this->po->fresh()->invoice_document;
        Storage::disk('r2')->assertExists($first);
        $this->assertFalse($this->po->fresh()->is_invoice_document_image);

        $this->actingAs($admin)->post(route('warehouse.receiving.upload-invoice', $this->po), [
            'invoice_document' => UploadedFile::fake()->image('retake.png'),
            'invoice_number' => 'INV-2',
        ])->assertSessionHas('success');
        $this->assertStringEndsWith('.png', $this->po->fresh()->invoice_document);
        $this->assertSame('INV-2', $this->po->fresh()->vendor_invoice_number);
    }

    public function test_receiving_page_shows_the_attached_invoice(): void
    {
        $this->po->update(['invoice_document' => 'invoices/invoice_1_1.jpg']);

        $this->actingAs($this->superAdmin())->get(route('warehouse.receiving.show', $this->po))
            ->assertOk()
            ->assertSee('invoices/invoice_1_1.jpg', false);
    }

    public function test_non_image_non_pdf_files_are_rejected(): void
    {
        $this->actingAs($this->superAdmin())->post(route('warehouse.receiving.upload-invoice', $this->po), [
            'invoice_document' => UploadedFile::fake()->create('invoice.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('invoice_document');

        $this->assertNull($this->po->fresh()->invoice_document);
    }
}
