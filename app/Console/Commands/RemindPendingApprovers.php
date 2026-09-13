<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\ApprovalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Item 16: nudge the external approver every 30 minutes while a PO sits
 * unapproved, so it doesn't get lost in an inbox. Stops naturally once
 * approval_status moves off 'pending' (approved/rejected).
 */
class RemindPendingApprovers extends Command
{
    protected $signature = 'po:remind-approvers';
    protected $description = 'Send a reminder email to the external approver for every PO still awaiting approval';

    public function handle(ApprovalService $approvalService): int
    {
        $pending = PurchaseOrder::where('approval_status', 'pending')
            ->whereNotNull('approval_email')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No purchase orders awaiting approval.');
            return self::SUCCESS;
        }

        $this->info("Reminding approvers for {$pending->count()} pending PO(s).");

        foreach ($pending as $po) {
            try {
                $approvalService->sendApprovalReminder($po);
                $this->line("  ✅ PO #{$po->po_number} → {$po->approval_email}");
            } catch (\Exception $e) {
                Log::error("[RemindPendingApprovers] Failed for PO #{$po->po_number}: " . $e->getMessage());
                $this->line("  ❌ PO #{$po->po_number}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
