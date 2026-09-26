<?php

namespace Tests\Feature;

use App\Mail\ApproverPORejected;
use App\Models\PurchaseOrder;
use App\Services\ApprovalService;
use App\Services\PurchaseOrderListing;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InsertsMinimalRows;
use Tests\TestCase;

/**
 * Client feedback (POSIssues 9/15, re-sent 9/26), Warehouse items 2-3: a
 * rejected PO must show a red REJECTED badge instead of "Draft", and the
 * approver who rejected it gets an email copy of the PO -- items and prices
 * included -- the same way an approver gets a copy after approving.
 */
class PurchaseOrderRejectionTest extends TestCase
{
    use InsertsMinimalRows;

    private function po(array $attributes = []): PurchaseOrder
    {
        $this->withoutForeignKeys();
        $vendorId = $this->insertRow('vendors', ['name' => 'Acme Foods']);
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', array_merge([
            'vendor_id' => $vendorId, 'po_number' => 'PO-TEST-' . uniqid(),
            'order_date' => now()->toDateString(), 'status' => 'draft',
            'approval_status' => 'pending', 'total_amount' => 50,
        ], $attributes)));
        $productId = $this->insertRow('products', ['product_name' => 'Bell Pepper', 'barcode' => '0001112223', 'store_id' => null]);
        $this->insertRow('purchase_order_items', [
            'purchase_order_id' => $po->id, 'product_id' => $productId,
            'requested_quantity' => 2, 'unit_cost' => 25, 'total_cost' => 50,
        ]);

        return $po;
    }

    private function badge(PurchaseOrder $po): string
    {
        return strip_tags((new \ReflectionMethod(PurchaseOrderListing::class, 'statusBadge'))
            ->invoke(app(PurchaseOrderListing::class), $po->fresh()));
    }

    public function test_rejecting_shows_rejected_in_the_list_and_emails_the_approver(): void
    {
        Mail::fake();
        $po = $this->po();

        app(ApprovalService::class)->processApproval($po, 'reject', 'approver@example.com', 'Too expensive');

        $this->assertSame('REJECTED', $this->badge($po));
        Mail::assertSent(ApproverPORejected::class, fn ($mail) => $mail->hasTo('approver@example.com'));
    }

    public function test_rejection_email_is_a_copy_of_the_po_with_items_and_prices(): void
    {
        $po = $this->po(['approval_status' => 'rejected', 'approval_reason' => 'Too expensive']);

        $html = (new ApproverPORejected($po))->render();

        $this->assertStringContainsString('Bell Pepper', $html);
        $this->assertStringContainsString('$25.00', $html);
        $this->assertStringContainsString('$50.00', $html);
        $this->assertStringContainsString('Too expensive', $html);
    }

    public function test_a_rejected_po_that_was_later_cancelled_shows_its_real_status(): void
    {
        $this->assertSame('CANCELLED', $this->badge($this->po(['status' => 'cancelled', 'approval_status' => 'rejected'])));
    }

    public function test_vendor_po_email_shows_real_unit_prices_not_zero(): void
    {
        $po = $this->po(['status' => 'ordered', 'approval_status' => 'approved']);

        $html = view('emails.vendor-purchase-order', ['po' => $po->load('items.product', 'vendor'), 'acknowledgeUrl' => '#', 'denyUrl' => '#'])->render();

        $this->assertStringContainsString('$25.00', $html);
        $this->assertStringNotContainsString('$0.00</td>', $html);
    }
}
