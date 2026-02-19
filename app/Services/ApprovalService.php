<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ApprovalService
{
    /**
     * Send approval email for a purchase order
     */
    public function sendApprovalEmail(PurchaseOrder $po)
    {
        if (!$po->approval_email) {
            throw new \Exception('No approval email specified for this PO');
        }

        // Generate signed URLs for approve/reject actions
        $approveUrl = URL::temporarySignedRoute(
            'warehouse.purchase-orders.approve',
            now()->addDays(7),
            ['purchaseOrder' => $po->id, 'action' => 'approve']
        );

        $rejectUrl = URL::temporarySignedRoute(
            'warehouse.purchase-orders.approve',
            now()->addDays(7),
            ['purchaseOrder' => $po->id, 'action' => 'reject']
        );

        // Send email
        Mail::send('emails.purchase-order-approval', [
            'po' => $po,
            'approveUrl' => $approveUrl,
            'rejectUrl' => $rejectUrl,
        ], function ($message) use ($po) {
            $message->to($po->approval_email)
                    ->subject("Purchase Order #{$po->po_number} - Approval Required");
        });

        return true;
    }

    /**
     * Process approval/rejection from email link
     */
    public function processApproval(PurchaseOrder $po, string $action, string $approverEmail, string $reason = null)
    {
        if ($action === 'approve') {
            $po->approve($approverEmail, $reason);
            $this->logApproval($po, $approverEmail, 'approved', $reason);
            return 'Purchase Order approved successfully';
        } elseif ($action === 'reject') {
            if (!$reason) {
                throw new \Exception('Rejection reason is required');
            }
            $po->reject($approverEmail, $reason);
            $this->logApproval($po, $approverEmail, 'rejected', $reason);
            return 'Purchase Order rejected';
        }

        throw new \Exception('Invalid action');
    }

    /**
     * Log approval action
     */
    protected function logApproval(PurchaseOrder $po, string $approverEmail, string $decision, string $reason = null)
    {
        // You can log to a separate approvals table or activity log
        \Log::info("PO #{$po->po_number} {$decision} by {$approverEmail}", [
            'po_id' => $po->id,
            'decision' => $decision,
            'reason' => $reason,
            'timestamp' => now(),
        ]);
    }
}
