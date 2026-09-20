<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\ApprovalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Public, signed-URL endpoints clicked from emails by people with no warehouse
 * login (the external approver and the vendor). Kept apart from
 * PurchaseOrderController, whose actions are all for logged-in staff.
 * The routes sit outside the auth group on purpose.
 */
class PurchaseOrderExternalController extends Controller
{
    public function __construct(private ApprovalService $approvalService)
    {
    }

    /**
     * Handle approval/rejection from email link
     */
    public function approval(Request $request, PurchaseOrder $purchaseOrder)
    {
        $action = $request->query('action'); // 'approve' or 'reject'

        if (!in_array($action, ['approve', 'reject'])) {
            return view('warehouse.purchase-orders.approval-result', [
                'success' => false,
                'message' => 'Invalid action',
            ]);
        }

        // If rejecting, show form to collect reason
        if ($action === 'reject' && !$request->has('reason')) {
            return view('warehouse.purchase-orders.rejection-form', compact('purchaseOrder'));
        }

        try {
            $approverEmail = $purchaseOrder->approval_email;
            $reason = $request->input('reason');

            // ApprovalService::processApproval() already notifies admins internally
            // (was also happening here — duplicated every "PO Approved"/"PO Rejected"
            // notification, matching the client-reported duplicate pairs).
            $message = $this->approvalService->processApproval(
                $purchaseOrder,
                $action,
                $approverEmail,
                $reason
            );

            $cancelUrl = $action === 'approve'
                ? URL::temporarySignedRoute(
                    'warehouse.purchase-orders.approver-cancel',
                    now()->addDays(14),
                    ['purchaseOrder' => $purchaseOrder->id]
                )
                : null;

            return view('warehouse.purchase-orders.approval-result', [
                'success' => true,
                'message' => $message,
                'po' => $purchaseOrder,
                'cancelUrl' => $cancelUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Approval processing failed: ' . $e->getMessage(), ['exception' => $e]);
            return view('warehouse.purchase-orders.approval-result', [
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ]);
        }
    }

    /**
     * Handle a vendor's acknowledge/deny response to a sent PO (from email link).
     * Mirrors approval() above but for the vendor's own response, which is
     * tracked separately from the internal approval workflow.
     */
    public function vendorResponse(Request $request, PurchaseOrder $purchaseOrder)
    {
        $action = $request->query('action'); // 'acknowledge' or 'deny'

        if (!in_array($action, ['acknowledge', 'deny'])) {
            return view('warehouse.purchase-orders.vendor-response-result', [
                'success' => false,
                'message' => 'Invalid action',
            ]);
        }

        // If denying, show a form to collect the reason first
        if ($action === 'deny' && !$request->has('reason')) {
            return view('warehouse.purchase-orders.vendor-denial-form', compact('purchaseOrder'));
        }

        try {
            if ($action === 'acknowledge') {
                $purchaseOrder->vendorAcknowledge();
                $message = "Thank you — PO #{$purchaseOrder->po_number} has been marked as acknowledged.";
            } else {
                $reason = $request->input('reason');
                if (!$reason) {
                    throw new \Exception('A reason is required to deny this order.');
                }
                $purchaseOrder->vendorDeny($reason);
                $message = "PO #{$purchaseOrder->po_number} has been marked as denied.";
            }

            NotificationService::sendToAdmins(
                'Vendor ' . ucfirst($action) . 'd Order',
                "Vendor {$purchaseOrder->vendor?->name} has {$action}d PO #{$purchaseOrder->po_number}" .
                    ($action === 'deny' ? ": {$purchaseOrder->vendor_denial_reason}" : '.'),
                $action === 'acknowledge' ? 'success' : 'warning',
                route('warehouse.purchase-orders.show', $purchaseOrder->id)
            );

            return view('warehouse.purchase-orders.vendor-response-result', [
                'success' => true,
                'message' => $message,
                'po' => $purchaseOrder,
            ]);
        } catch (\Exception $e) {
            Log::error('Vendor response processing failed: ' . $e->getMessage(), ['exception' => $e]);
            return view('warehouse.purchase-orders.vendor-response-result', [
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ]);
        }
    }

    /**
     * Let the external approver cancel a PO they just approved — via the same
     * signed-link mechanism, since they have no warehouse login (item 16).
     */
    public function approverCancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_DRAFT])) {
            return view('warehouse.purchase-orders.approval-result', [
                'success' => false,
                'message' => 'This purchase order can no longer be cancelled (it has already progressed to receiving or been cancelled).',
            ]);
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        NotificationService::sendToAdmins(
            'PO Cancelled by Approver',
            "PO #{$purchaseOrder->po_number} was cancelled by the approver ({$purchaseOrder->approved_by_email}).",
            'warning',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );

        return view('warehouse.purchase-orders.approval-result', [
            'success' => true,
            'message' => "PO #{$purchaseOrder->po_number} has been cancelled.",
            'po' => $purchaseOrder,
        ]);
    }
}
