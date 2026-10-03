<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ApprovalService
{
    /**
     * Send approval email for a purchase order
     */
    public function sendApprovalEmail(PurchaseOrder $po, bool $isReminder = false)
    {
        if (!$po->approval_email) {
            throw new \App\Exceptions\BusinessRuleException('No approval email specified for this PO');
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

        // Never log the links: anyone reading the logs could approve the PO.
        Log::info("PO #{$po->po_number}: sending approval email to {$po->approval_email}" . ($isReminder ? ' (reminder)' : ''));

        // Send email
        Mail::send('emails.purchase-order-approval', [
            'po' => $po,
            'approveUrl' => $approveUrl,
            'rejectUrl' => $rejectUrl,
            'isReminder' => $isReminder,
        ], function ($message) use ($po, $isReminder) {
            $subject = $isReminder
                ? "Reminder: Purchase Order #{$po->po_number} - Approval Still Needed"
                : "Purchase Order #{$po->po_number} - Approval Required";
            $message->to($po->approval_email)->subject($subject);
        });

        return true;
    }

    /**
     * Called when the approver confirms their email (client issue 10/1): a PO
     * still waiting as a draft that has not been sent yet is sent for approval
     * right away. Returns 'sent', 'failed', or null when nothing was due (no
     * items, already sent / approved / rejected, or not a draft any more).
     */
    public function sendAfterEmailConfirmed(PurchaseOrder $po): ?string
    {
        if ($po->status !== PurchaseOrder::STATUS_DRAFT
            || $po->approval_status !== 'draft'
            || !$po->approval_email
            || !$po->items()->exists()) {
            return null;
        }

        try {
            $this->sendApprovalEmail($po);
        } catch (\Throwable $e) {
            Log::error("PO #{$po->po_number}: approval email after confirmation failed: " . $e->getMessage());
            NotificationService::sendToAdmins(
                'Approval email not sent',
                "{$po->approval_email} confirmed their email for PO #{$po->po_number}, but the approval email could not be sent: "
                    . \App\Support\MailFailure::reason($e) . ' Use "Send order for approval" to retry.',
                'danger',
                route('warehouse.purchase-orders.show', $po->id)
            );

            return 'failed';
        }

        $po->update(['approval_status' => PurchaseOrder::APPROVAL_PENDING]);
        NotificationService::sendToAdmins(
            'PO sent for approval',
            "{$po->approval_email} confirmed their email, so PO #{$po->po_number} was sent to them for approval.",
            'info',
            route('warehouse.purchase-orders.show', $po->id)
        );

        return 'sent';
    }

    /**
     * Same email as sendApprovalEmail(), reworded as a reminder — used by the
     * every-30-minutes nudge for POs still sitting unapproved (item 16).
     */
    public function sendApprovalReminder(PurchaseOrder $po)
    {
        return $this->sendApprovalEmail($po, true);
    }

    /**
     * Process approval/rejection from email link
     */
    public function processApproval(PurchaseOrder $po, string $action, string $approverEmail, ?string $reason = null)
    {
        if ($action === 'approve') {
            $po->approve($approverEmail, $reason);
            $this->logApproval($po, $approverEmail, 'approved', $reason);

            // Automatically send to vendor upon approval — the approver gets a
            // bcc'd copy of the exact email that went to the vendor.
            try {
                $vendorComm = app(\App\Services\VendorCommunicationService::class);
                $vendorComm->sendPOToVendor($po, true, false, $approverEmail);
            } catch (\Exception $e) {
                \Log::error("Failed to auto-send PO #{$po->po_number} to vendor: " . $e->getMessage());
            }

            // Separate confirmation email to the approver with a durable cancel
            // link — usable anytime, not just on the immediate result page.
            try {
                $cancelUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'warehouse.purchase-orders.approver-cancel',
                    now()->addDays(14),
                    ['purchaseOrder' => $po->id]
                );
                \Illuminate\Support\Facades\Mail::to($approverEmail)
                    ->send(new \App\Mail\ApproverPOConfirmation($po, $cancelUrl));
            } catch (\Exception $e) {
                \Log::error("Failed to send approver confirmation for PO #{$po->po_number}: " . $e->getMessage());
            }

            \App\Services\NotificationService::sendToAdmins(
                'PO Approved',
                "Purchase Order #{$po->po_number} has been approved by external approver.",
                'success',
                route('warehouse.purchase-orders.show', $po->id)
            );

            return 'Purchase Order approved successfully';
        } elseif ($action === 'reject') {
            if (!$reason) {
                throw new \App\Exceptions\BusinessRuleException('Rejection reason is required');
            }
            $po->reject($approverEmail, $reason);
            $this->logApproval($po, $approverEmail, 'rejected', $reason);

            try {
                \Illuminate\Support\Facades\Mail::to($approverEmail)
                    ->send(new \App\Mail\ApproverPORejected($po));
            } catch (\Exception $e) {
                \Log::error("Failed to send approver rejection confirmation for PO #{$po->po_number}: " . $e->getMessage());
            }

            \App\Services\NotificationService::sendToAdmins(
                'PO Rejected',
                "Purchase Order #{$po->po_number} has been rejected. Reason: {$reason}",
                'danger',
                route('warehouse.purchase-orders.show', $po->id)
            );

            return 'Purchase Order rejected';
        }

        throw new \App\Exceptions\BusinessRuleException('Invalid action');
    }

    /**
     * Log approval action
     */
    protected function logApproval(PurchaseOrder $po, string $approverEmail, string $decision, ?string $reason = null)
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
