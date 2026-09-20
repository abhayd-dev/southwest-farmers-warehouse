<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Pins down the current Purchase Order behaviour (staff actions, DataTable
 * feed, and the signed email-link handlers) so the controller can be
 * refactored without changing what users see.
 */
class PurchaseOrderWorkflowTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private int $vendorId;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutForeignKeys();

        $this->vendorId = $this->insertRow('vendors', ['name' => 'Acme Foods']);
        $this->productId = $this->insertRow('products', ['product_name' => 'Rice 10kg', 'store_id' => null]);
    }

    private function po(array $attributes = []): PurchaseOrder
    {
        return PurchaseOrder::find($this->insertRow('purchase_orders', $attributes + [
            'vendor_id' => $this->vendorId,
            'po_number' => 'PO-TEST-' . uniqid(),
            'order_date' => now()->toDateString(),
            'status' => 'draft',
        ]));
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'vendor_id' => $this->vendorId,
            'order_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->productId, 'quantity' => 2, 'cost' => 5],
                ['product_id' => $this->productId, 'quantity' => 1, 'cost' => 7.5],
            ],
        ];
    }

    // ---- create / edit / update ------------------------------------------

    public function test_store_creates_a_draft_po_with_items_and_total(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('warehouse.purchase-orders.store'), $this->payload());

        $po = PurchaseOrder::firstOrFail();
        $response->assertRedirect(route('warehouse.purchase-orders.show', $po->id))->assertSessionHas('success');
        $this->assertSame('draft', $po->status);
        $this->assertSame('draft', $po->approval_status);
        $this->assertEquals(17.5, (float) $po->total_amount);
        $this->assertSame($admin->id, (int) $po->created_by);
        $this->assertDatabaseCount('purchase_order_items', 2);
    }

    public function test_store_rejects_invalid_input_and_creates_nothing(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('warehouse.purchase-orders.store'), ['vendor_id' => $this->vendorId])
            ->assertSessionHasErrors(['order_date', 'items']);

        $this->actingAs($this->superAdmin())
            ->post(route('warehouse.purchase-orders.store'), $this->payload(['approval_email' => 'not-an-email']))
            ->assertSessionHasErrors('approval_email');

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_only_draft_pos_can_be_edited_or_updated(): void
    {
        $admin = $this->superAdmin();
        $ordered = $this->po(['status' => 'ordered']);

        $this->actingAs($admin)->get(route('warehouse.purchase-orders.edit', $ordered))
            ->assertRedirect()->assertSessionHas('error', 'Only draft purchase orders can be edited.');

        $this->actingAs($admin)->put(route('warehouse.purchase-orders.update', $ordered), $this->payload())
            ->assertRedirect()->assertSessionHas('error', 'Only draft purchase orders can be edited.');
    }

    public function test_a_draft_po_can_be_updated(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post(route('warehouse.purchase-orders.store'), $this->payload());
        $po = PurchaseOrder::firstOrFail();

        $this->actingAs($admin)->put(route('warehouse.purchase-orders.update', $po), $this->payload([
            'items' => [['product_id' => $this->productId, 'quantity' => 4, 'cost' => 2]],
        ]))->assertRedirect(route('warehouse.purchase-orders.show', $po->id))->assertSessionHas('success');

        $this->assertEquals(8.0, (float) $po->refresh()->total_amount);
        $this->assertDatabaseCount('purchase_order_items', 1);
    }

    // ---- status transitions and their guards -----------------------------

    public function test_mark_ordered_needs_approve_po_and_a_draft(): void
    {
        $draft = $this->po();

        $this->actingAs($this->userWithPermissions(['view_po']))
            ->post(route('warehouse.purchase-orders.mark-ordered', $draft))->assertForbidden();
        $this->assertSame('draft', $draft->refresh()->status);

        $this->actingAs($this->userWithPermissions(['approve_po']))
            ->post(route('warehouse.purchase-orders.mark-ordered', $draft))->assertSessionHas('success');
        $this->assertSame('ordered', $draft->refresh()->status);

        $this->actingAs($this->superAdmin())
            ->post(route('warehouse.purchase-orders.mark-ordered', $draft))->assertForbidden();
    }

    public function test_cancel_is_allowed_for_draft_and_ordered_only(): void
    {
        $admin = $this->superAdmin();

        foreach (['draft', 'ordered'] as $status) {
            $po = $this->po(['status' => $status]);
            $this->actingAs($admin)->post(route('warehouse.purchase-orders.cancel', $po))->assertSessionHas('success');
            $this->assertSame('cancelled', $po->refresh()->status);
        }

        $completed = $this->po(['status' => 'completed']);
        $this->actingAs($admin)->post(route('warehouse.purchase-orders.cancel', $completed))->assertForbidden();

        $this->actingAs($this->userWithPermissions(['view_po']))
            ->post(route('warehouse.purchase-orders.cancel', $this->po()))->assertForbidden();
    }

    public function test_mark_completed_needs_receive_po_and_a_partial_po(): void
    {
        $partial = $this->po(['status' => 'partial']);

        $this->actingAs($this->userWithPermissions(['view_po']))
            ->post(route('warehouse.purchase-orders.mark-completed', $partial))->assertForbidden();

        $this->actingAs($this->userWithPermissions(['receive_po']))
            ->post(route('warehouse.purchase-orders.mark-completed', $partial))->assertSessionHas('success');
        $this->assertSame('completed', $partial->refresh()->status);

        $this->actingAs($this->superAdmin())
            ->post(route('warehouse.purchase-orders.mark-completed', $this->po(['status' => 'draft'])))->assertForbidden();
    }

    public function test_revert_to_draft_is_blocked_for_completed_orders(): void
    {
        $admin = $this->superAdmin();
        $ordered = $this->po(['status' => 'ordered']);
        $completed = $this->po(['status' => 'completed']);

        $this->actingAs($admin)->post(route('warehouse.purchase-orders.revert-draft', $ordered))->assertSessionHas('success');
        $this->assertSame('draft', $ordered->refresh()->status);

        $this->actingAs($admin)->post(route('warehouse.purchase-orders.revert-draft', $completed))
            ->assertSessionHas('error', 'Cannot revert a completed or received purchase order.');
        $this->assertSame('completed', $completed->refresh()->status);
    }

    public function test_send_approval_requires_an_approval_email_and_marks_the_po_pending(): void
    {
        $admin = $this->superAdmin();

        $noEmail = $this->po();
        $this->actingAs($admin)->post(route('warehouse.purchase-orders.send-approval', $noEmail))
            ->assertSessionHas('error', 'No approval email set for this PO. Please edit the PO to add one.');

        $withEmail = $this->po(['approval_email' => 'approver@example.com']);
        $this->actingAs($admin)->post(route('warehouse.purchase-orders.send-approval', $withEmail));
        $this->assertSame('pending', $withEmail->refresh()->approval_status);

        $this->actingAs($this->userWithPermissions(['view_po']))
            ->post(route('warehouse.purchase-orders.send-approval', $withEmail))->assertForbidden();
    }

    public function test_a_rejected_po_cannot_be_sent_to_the_vendor(): void
    {
        $po = $this->po(['approval_status' => 'rejected']);

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.send-to-vendor', $po))
            ->assertSessionHas('error', 'Cannot send a rejected Purchase Order to vendor. Please resubmit order for approval first.');
    }

    public function test_cost_increase_approval_is_super_admin_only(): void
    {
        $po = $this->po();

        $this->actingAs($this->userWithPermissions(['approve_po']))
            ->post(route('warehouse.purchase-orders.approve-cost', $po))
            ->assertRedirect(route('warehouse.purchase-orders.index'))->assertSessionHas('error', 'Unauthorized access.');
        $this->assertFalse((bool) $po->refresh()->cost_increase_approved);

        $this->actingAs($this->superAdmin())->post(route('warehouse.purchase-orders.approve-cost', $po));
        $this->assertTrue((bool) $po->refresh()->cost_increase_approved);
    }

    // ---- list feed --------------------------------------------------------

    public function test_stats_endpoint_summarises_pos_by_status(): void
    {
        $this->po(['status' => 'ordered', 'total_amount' => 100]);
        $this->po(['status' => 'partial', 'total_amount' => 50]);
        $this->po(['status' => 'completed', 'total_amount' => 25]);

        $stats = $this->actingAs($this->superAdmin())
            ->getJson(route('warehouse.purchase-orders.index', ['stats' => 1]))
            ->assertOk()->json('stats');

        $this->assertSame(3, $stats['total']);
        $this->assertSame(2, $stats['pending']);
        $this->assertSame(1, $stats['completed']);
        $this->assertSame('175', $stats['value']);
        ksort($stats['by_status']);
        $this->assertSame(['completed' => 1, 'ordered' => 1, 'partial' => 1], $stats['by_status']);
    }

    private function listNumbers(array $query = []): array
    {
        $rows = $this->actingAs($this->superAdmin())
            ->getJson(route('warehouse.purchase-orders.index', $query), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');

        return collect($rows)->pluck('po_number')->sort()->values()->all();
    }

    public function test_the_list_hides_completed_and_cancelled_by_default_and_filters_by_status(): void
    {
        // approval_status is NOT NULL (default 'pending') and the app itself saves
        // new POs with 'draft', so that is what real draft rows look like.
        $this->po(['po_number' => 'A-DRAFT', 'status' => 'draft', 'approval_status' => 'draft']);
        $this->po(['po_number' => 'B-WAITING', 'status' => 'draft', 'approval_status' => 'pending']);
        $this->po(['po_number' => 'C-APPROVED', 'status' => 'draft', 'approval_status' => 'approved']);
        $this->po(['po_number' => 'D-ORDERED', 'status' => 'ordered', 'approval_status' => 'approved']);
        $this->po(['po_number' => 'E-COMPLETED', 'status' => 'completed', 'approval_status' => 'approved']);
        $this->po(['po_number' => 'F-CANCELLED', 'status' => 'cancelled', 'approval_status' => 'approved']);
        $this->po(['po_number' => 'G-REJECTED', 'status' => 'draft', 'approval_status' => 'rejected']);

        $this->assertSame(['A-DRAFT', 'B-WAITING', 'C-APPROVED', 'D-ORDERED', 'G-REJECTED'], $this->listNumbers());
        $this->assertSame(['B-WAITING'], $this->listNumbers(['status' => 'pending_approval']));
        $this->assertSame(['C-APPROVED'], $this->listNumbers(['status' => 'approved']));
        $this->assertSame(['D-ORDERED'], $this->listNumbers(['status' => 'ordered']));
        $this->assertSame(['E-COMPLETED'], $this->listNumbers(['status' => 'completed']));
    }

    public function test_the_draft_tab_lists_draft_pos(): void
    {
        // Regression: the "Draft" filter used whereNull('approval_status'), which can
        // never match (the column is NOT NULL and new POs are saved as 'draft'), so
        // the tab was always empty even with draft POs in the system.
        $this->po(['po_number' => 'A-DRAFT', 'status' => 'draft', 'approval_status' => 'draft']);
        $this->po(['po_number' => 'B-WAITING', 'status' => 'draft', 'approval_status' => 'pending']);
        $this->po(['po_number' => 'G-REJECTED', 'status' => 'draft', 'approval_status' => 'rejected']);

        $this->assertSame(['A-DRAFT'], $this->listNumbers(['status' => 'draft']));
    }

    public function test_the_list_filters_by_po_number_vendor_and_date(): void
    {
        $other = $this->insertRow('vendors', ['name' => 'Zed Traders']);
        $this->po(['po_number' => 'PO-ONE', 'order_date' => '2026-01-10']);
        $this->po(['po_number' => 'PO-TWO', 'vendor_id' => $other, 'order_date' => '2026-03-10']);

        $this->assertSame(['PO-ONE'], $this->listNumbers(['po_number' => 'ONE']));
        $this->assertSame(['PO-TWO'], $this->listNumbers(['vendor' => 'zed']) === [] ? $this->listNumbers(['vendor' => 'Zed']) : $this->listNumbers(['vendor' => 'zed']));
        $this->assertSame(['PO-TWO'], $this->listNumbers(['date_from' => '2026-02-01']));
        $this->assertSame(['PO-ONE'], $this->listNumbers(['date_to' => '2026-02-01']));
    }

    public function test_status_badges_are_rendered_for_each_state(): void
    {
        $this->po(['po_number' => 'B1', 'status' => 'draft', 'approval_status' => 'pending']);
        $this->po(['po_number' => 'B2', 'status' => 'draft', 'approval_status' => 'rejected']);
        $this->po(['po_number' => 'B3', 'status' => 'partial']);
        $this->po(['po_number' => 'B4', 'status' => 'ordered']);

        $rows = collect($this->actingAs($this->superAdmin())
            ->getJson(route('warehouse.purchase-orders.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->json('data'))->keyBy('po_number');

        $this->assertStringContainsString('WAITING FOR APPROVAL', $rows['B1']['status_badge']);
        $this->assertStringContainsString('REJECTED', $rows['B2']['status_badge']);
        $this->assertStringContainsString('IN TRANSIT', $rows['B3']['status_badge']);
        $this->assertStringContainsString('bg-info', $rows['B4']['status_badge']);
        $this->assertStringContainsString('View', $rows['B4']['action']);
    }

    // ---- signed email-link handlers (no login) ---------------------------

    private function signed(string $route, PurchaseOrder $po, array $query = []): string
    {
        return URL::temporarySignedRoute($route, now()->addDay(), ['purchaseOrder' => $po->id] + $query);
    }

    public function test_the_external_approver_can_approve_without_logging_in(): void
    {
        $this->superAdmin();
        $po = $this->po(['approval_email' => 'approver@example.com', 'approval_status' => 'pending']);

        $this->get($this->signed('warehouse.purchase-orders.approve', $po, ['action' => 'approve']))
            ->assertOk()->assertViewIs('warehouse.purchase-orders.approval-result')
            ->assertViewHas('success', true)->assertViewHas('cancelUrl');

        $po->refresh();
        $this->assertSame('approved', $po->approval_status);
        $this->assertSame('ordered', $po->status);
    }

    public function test_rejecting_first_asks_for_a_reason_then_rejects(): void
    {
        $this->superAdmin();
        $po = $this->po(['approval_email' => 'approver@example.com', 'approval_status' => 'pending']);
        $url = $this->signed('warehouse.purchase-orders.approve', $po, ['action' => 'reject']);

        $this->get($url)->assertOk()->assertViewIs('warehouse.purchase-orders.rejection-form');
        $this->assertSame('pending', $po->refresh()->approval_status);

        $this->get($url . '&reason=Too+expensive')
            ->assertOk()->assertViewIs('warehouse.purchase-orders.approval-result')->assertViewHas('success', true);
        $this->assertSame('rejected', $po->refresh()->approval_status);
    }

    public function test_an_invalid_approval_action_is_rejected_gracefully(): void
    {
        $po = $this->po();

        $this->get($this->signed('warehouse.purchase-orders.approve', $po, ['action' => 'hack']))
            ->assertOk()->assertViewHas('success', false)->assertViewHas('message', 'Invalid action');
    }

    public function test_approval_links_need_a_valid_signature(): void
    {
        $po = $this->po();

        $this->get(route('warehouse.purchase-orders.approve', ['purchaseOrder' => $po->id, 'action' => 'approve']))->assertForbidden();
        $this->get(route('warehouse.purchase-orders.approver-cancel', $po))->assertForbidden();
        $this->get(route('warehouse.purchase-orders.vendor-response', ['purchaseOrder' => $po->id, 'action' => 'acknowledge']))->assertForbidden();
    }

    public function test_the_vendor_can_acknowledge_or_deny_with_a_reason(): void
    {
        $this->superAdmin();
        $po = $this->po(['status' => 'ordered']);

        $this->get($this->signed('warehouse.purchase-orders.vendor-response', $po, ['action' => 'acknowledge']))
            ->assertOk()->assertViewIs('warehouse.purchase-orders.vendor-response-result')->assertViewHas('success', true);
        $this->assertSame('acknowledged', $po->refresh()->vendor_response_status);

        $denyUrl = $this->signed('warehouse.purchase-orders.vendor-response', $po, ['action' => 'deny']);
        $this->get($denyUrl)->assertOk()->assertViewIs('warehouse.purchase-orders.vendor-denial-form');

        $this->get($denyUrl . '&reason=Out+of+stock')->assertOk()->assertViewHas('success', true);
        $po->refresh();
        $this->assertSame('denied', $po->vendor_response_status);
        $this->assertSame('Out of stock', $po->vendor_denial_reason);
    }

    public function test_the_approver_can_cancel_only_while_the_po_is_still_draft_or_ordered(): void
    {
        $this->superAdmin();
        $ordered = $this->po(['status' => 'ordered', 'approved_by_email' => 'approver@example.com']);
        $partial = $this->po(['status' => 'partial']);

        $this->get($this->signed('warehouse.purchase-orders.approver-cancel', $ordered))
            ->assertOk()->assertViewHas('success', true);
        $this->assertSame('cancelled', $ordered->refresh()->status);

        $this->get($this->signed('warehouse.purchase-orders.approver-cancel', $partial))
            ->assertOk()->assertViewHas('success', false);
        $this->assertSame('partial', $partial->refresh()->status);
    }
}
