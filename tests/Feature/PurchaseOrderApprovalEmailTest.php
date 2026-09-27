<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * "Send order for approval": the PO only becomes "waiting for approval" once
 * the email has actually gone out (client: email not sent, but the button
 * state still changed).
 */
class PurchaseOrderApprovalEmailTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();
    }

    private function po(string $approvalStatus = 'draft'): PurchaseOrder
    {
        return PurchaseOrder::find($this->insertRow('purchase_orders', [
            'vendor_id' => $this->insertRow('vendors', ['name' => 'Taste Afrik']),
            'po_number' => 'PO-TEST-' . uniqid(),
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'approval_status' => $approvalStatus,
            'approval_email' => 'approver@example.com',
        ]));
    }

    private function mailServiceRejectsLogin(): void
    {
        Mail::shouldReceive('send')->andThrow(new TransportException(
            'Failed to authenticate on SMTP server with username "apikey" using the following authenticators: "LOGIN", "PLAIN". '
            . 'Authenticator "LOGIN" returned "Expected response code "235" but got code "535", with message "535 Authentication failed: The provided authorization grant is invalid, expired, or revoked"."'
        ));
    }

    public function test_a_sent_email_puts_the_po_into_waiting_for_approval(): void
    {
        $po = $this->po();

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.send-approval', $po))
            ->assertSessionHas('success', 'Approval email sent successfully to approver@example.com');

        $this->assertSame(PurchaseOrder::APPROVAL_PENDING, $po->refresh()->approval_status);
    }

    public function test_a_failed_email_leaves_the_po_as_draft_and_says_why(): void
    {
        $po = $this->po();
        $this->mailServiceRejectsLogin();

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.send-approval', $po))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'was NOT sent')
                && str_contains($message, '535 Authentication failed')
                && str_contains($message, 'update MAIL_PASSWORD'));

        $this->assertSame('draft', $po->refresh()->approval_status);
    }

    public function test_a_pending_po_can_resend_the_email_and_stays_pending(): void
    {
        $po = $this->po('pending');

        $html = $this->actingAs($this->superAdmin())->get(route('warehouse.purchase-orders.show', $po))->assertOk()->getContent();
        $this->assertStringContainsString('Resend approval email', $html);

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.send-approval', $po))
            ->assertSessionHas('success', 'Approval email sent again to approver@example.com');
        $this->assertSame(PurchaseOrder::APPROVAL_PENDING, $po->refresh()->approval_status);
    }
}
