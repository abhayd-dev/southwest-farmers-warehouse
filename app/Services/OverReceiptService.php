<?php

namespace App\Services;

use App\Mail\OverReceiptApprovalRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Client PDF 9/24, Warehouse item 1: when more is received than was ordered,
 * the stock is still received but the order is flagged and sent to the
 * approver. Approving makes the invoice quantity the received quantity;
 * rejecting keeps it at what was originally ordered.
 */
class OverReceiptService
{
    public function requestApproval(PurchaseOrder $po): void
    {
        $po->refresh();

        NotificationService::sendToAdmins(
            'Over-receipt needs approval',
            "PO #{$po->po_number}: more was received than ordered on " . count($po->over_receipt_lines ?? []) . ' line(s).',
            'warning',
            route('warehouse.purchase-orders.show', $po->id)
        );

        if (! $po->approval_email) {
            return;
        }

        try {
            Mail::to($po->approval_email)->send(new OverReceiptApprovalRequest(
                $po,
                $this->decisionUrl($po, 'approve'),
                $this->decisionUrl($po, 'reject'),
            ));
        } catch (\Throwable $e) {
            // Receiving already happened; the in-app Approve / Reject buttons still work.
            Log::error("Over-receipt approval email for PO #{$po->po_number} failed: " . $e->getMessage());
        }
    }

    public function decisionUrl(PurchaseOrder $po, string $decision): string
    {
        return URL::temporarySignedRoute(
            'warehouse.purchase-orders.over-receipt.decide',
            now()->addDays(14),
            ['purchaseOrder' => $po->id, 'decision' => $decision]
        );
    }

    /**
     * @return bool false if there was nothing pending to decide (already decided).
     */
    public function decide(PurchaseOrder $po, string $decision, string $decidedBy): bool
    {
        if (! in_array($decision, ['approve', 'reject'], true)) {
            throw new \InvalidArgumentException('Invalid decision');
        }

        $decided = DB::transaction(function () use ($po, $decision, $decidedBy) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($po->id);
            if ($po->over_receipt_status !== PurchaseOrder::OVER_RECEIPT_PENDING) {
                return false;
            }

            foreach ($po->over_receipt_lines ?? [] as $line) {
                $item = $po->items()->find($line['item_id']);
                if (! $item) {
                    continue;
                }
                $item->requested_quantity = $decision === 'approve'
                    ? max((int) $item->requested_quantity, (int) $item->received_quantity)
                    : (int) $line['ordered'];
                $item->save();
            }

            $po->total_amount = $po->items()->get()->sum(fn ($i) => $i->requested_quantity * $i->unit_cost);
            $po->over_receipt_status = $decision === 'approve' ? PurchaseOrder::OVER_RECEIPT_APPROVED : PurchaseOrder::OVER_RECEIPT_REJECTED;
            $po->over_receipt_decided_by = $decidedBy;
            $po->over_receipt_decided_at = now();
            $po->save();

            return true;
        });

        if ($decided) {
            NotificationService::sendToAdmins(
                $decision === 'approve' ? 'Over-receipt approved' : 'Over-receipt rejected',
                "PO #{$po->po_number}: over-receipt " . ($decision === 'approve' ? 'approved' : 'rejected') . " by {$decidedBy}. "
                    . ($decision === 'approve' ? 'Invoice now reflects the received quantity.' : 'Invoice stays at the ordered quantity.'),
                $decision === 'approve' ? 'success' : 'danger',
                route('warehouse.purchase-orders.show', $po->id)
            );
        }

        return $decided;
    }
}
