<?php

namespace Tests\Feature;

use App\Mail\VerifyContactEmail;
use App\Models\PurchaseOrder;
use App\Models\WareNotification;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client issue 10/1: after the approver confirms their email, the approval
 * email should go out on its own; and an approver who already confirmed is
 * not asked to confirm again for every new PO.
 */
class ApproverEmailConfirmedTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
        $this->superAdmin(); // receives the in-app notifications
    }

    private function po(array $attrs = [], bool $withItem = true): PurchaseOrder
    {
        $po = PurchaseOrder::find($this->insertRow('purchase_orders', $attrs + [
            'vendor_id' => $this->insertRow('vendors', ['name' => 'Accounting Testing']),
            'po_number' => 'PO-TEST-' . uniqid(),
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'approval_status' => 'draft',
            'approval_email' => 'eodom@example.com',
        ]));
        if ($withItem) {
            $this->insertRow('purchase_order_items', ['purchase_order_id' => $po->id, 'product_id' => $this->insertRow('products', ['product_name' => 'Beef']), 'requested_quantity' => 1, 'unit_cost' => 128.70, 'total_cost' => 128.70]);
        }

        return $po;
    }

    private function confirmLink(PurchaseOrder $po): string
    {
        return URL::temporarySignedRoute('contact-email.verify', now()->addDays(7), ['type' => 'po_approval', 'id' => $po->id, 'email' => $po->approval_email]);
    }

    public function test_confirming_sends_the_approval_email_and_marks_the_po_pending(): void
    {
        $po = $this->po();
        Mail::shouldReceive('send')->once();

        $this->get($this->confirmLink($po))
            ->assertOk()
            ->assertSee('Email Verified')
            ->assertSee('has been sent to you for approval');

        $po->refresh();
        $this->assertNotNull($po->approval_email_verified_at);
        $this->assertSame(PurchaseOrder::APPROVAL_PENDING, $po->approval_status);
        $this->assertTrue(WareNotification::where('title', 'PO sent for approval')->exists());
    }

    public function test_clicking_the_link_again_does_not_send_a_second_approval_email(): void
    {
        $po = $this->po(['approval_status' => PurchaseOrder::APPROVAL_PENDING]);
        Mail::shouldReceive('send')->never();

        $this->get($this->confirmLink($po))->assertOk()->assertDontSee('has been sent to you for approval');
        $this->assertSame(PurchaseOrder::APPROVAL_PENDING, $po->refresh()->approval_status);
    }

    public function test_a_po_with_no_items_is_not_sent(): void
    {
        $po = $this->po([], withItem: false);
        Mail::shouldReceive('send')->never();

        $this->get($this->confirmLink($po))->assertOk();
        $this->assertSame('draft', $po->refresh()->approval_status);
    }

    public function test_a_failed_send_keeps_the_draft_and_tells_the_warehouse(): void
    {
        $po = $this->po();
        Mail::shouldReceive('send')->andThrow(new TransportException('451 Maximum credits exceeded'));

        $this->get($this->confirmLink($po))->assertOk()->assertSee('could not be sent yet');

        $po->refresh();
        $this->assertNotNull($po->approval_email_verified_at); // the address itself is confirmed
        $this->assertSame('draft', $po->approval_status);
        $this->assertTrue(WareNotification::where('title', 'Approval email not sent')->exists());
    }

    public function test_an_approver_confirmed_before_is_not_asked_again(): void
    {
        $this->po(['approval_email' => 'EOdom@example.com', 'approval_email_verified_at' => now()->subDay()]);
        Mail::fake();
        $this->actingAs($this->superAdmin());

        $po = app(PurchaseOrderService::class)->createPO([
            'vendor_id' => $this->insertRow('vendors', ['name' => 'V2']),
            'order_date' => now()->toDateString(),
            'approval_email' => 'eodom@example.com',
            'items' => [['product_id' => $this->insertRow('products', ['product_name' => 'Rice']), 'quantity' => 1, 'cost' => 5]],
        ]);

        $this->assertNotNull($po->refresh()->approval_email_verified_at);
        Mail::assertNotSent(VerifyContactEmail::class);
    }

    public function test_a_new_approver_still_gets_the_confirm_email(): void
    {
        Mail::fake();
        $this->actingAs($this->superAdmin());

        $po = app(PurchaseOrderService::class)->createPO([
            'vendor_id' => $this->insertRow('vendors', ['name' => 'V3']),
            'order_date' => now()->toDateString(),
            'approval_email' => 'new.approver@example.com',
            'items' => [['product_id' => $this->insertRow('products', ['product_name' => 'Rice']), 'quantity' => 1, 'cost' => 5]],
        ]);

        $this->assertNull($po->refresh()->approval_email_verified_at);
        Mail::assertSent(VerifyContactEmail::class, fn ($m) => $m->hasTo('new.approver@example.com'));
    }
}
