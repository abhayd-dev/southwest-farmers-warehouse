<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\StorePurchaseOrder;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderLateAlert;

class CheckStoreOrderAlerts extends Command
{
    protected $signature   = 'store-orders:check-alerts';
    protected $description = 'Check for pending/stale store POs and send escalation alerts';

    public function handle(): int
    {
        $this->info('Checking store order alerts...');

        $this->checkStalePending();
        $this->checkStaleApproved();

        $this->info('Store order alert check complete.');
        return self::SUCCESS;
    }

    /**
     * Level 1: POs still PENDING after 24 hours → alert warehouse admins
     */
    private function checkStalePending(): void
    {
        $stalePOs = StorePurchaseOrder::where('status', StorePurchaseOrder::STATUS_PENDING)
            ->where('created_at', '<=', now()->subHours(24))
            ->with('store')
            ->get();

        if ($stalePOs->isEmpty()) {
            $this->line('  No stale pending POs found.');
            return;
        }

        $this->warn("  Found {$stalePOs->count()} pending POs older than 24 hours.");

        foreach ($stalePOs as $po) {
            $hoursOld = (int) $po->created_at->diffInHours(now());

            $title   = "⏰ Pending PO Needs Attention";
            $message = "Store PO #{$po->po_number} for {$po->store->store_name} has been pending for {$hoursOld} hours.";

            // In-app notification
            NotificationService::sendToAdmins(
                $title,
                $message,
                'warning',
                route('warehouse.store-orders.show', $po->id)
            );

            // Log it
            NotificationLog::record(
                notificationFor: NotificationLog::FOR_STORE_PO_ALERT,
                message: $message,
                type: NotificationLog::TYPE_IN_APP,
                recipient: 'system',
                relatedId: $po->id
            );

            // Send Email Alert
            $adminEmail = env('WAREHOUSE_EMAIL', 'admin@southwestfarmers.com');
            try {
                Mail::to($adminEmail)->send(new OrderLateAlert($po));
                $this->line("     📩 Email alert sent to {$adminEmail}");
            } catch (\Exception $e) {
                Log::error('[StoreOrderAlerts] Email failed: ' . $e->getMessage());
            }

            $this->line("  → Alerted for PO #{$po->po_number} ({$hoursOld}h old)");
        }
    }

    /**
     * Level 2: POs APPROVED but not dispatched after 48 hours → escalation
     */
    private function checkStaleApproved(): void
    {
        $staleApproved = StorePurchaseOrder::where('status', StorePurchaseOrder::STATUS_APPROVED)
            ->where('approved_at', '<=', now()->subHours(48))
            ->with('store')
            ->get();

        if ($staleApproved->isEmpty()) {
            $this->line('  No stale approved POs found.');
            return;
        }

        $this->warn("  Found {$staleApproved->count()} approved POs not dispatched after 48 hours.");

        foreach ($staleApproved as $po) {
            $hoursOld = (int) $po->approved_at->diffInHours(now());

            $title   = "🚨 Approved PO Not Dispatched";
            $message = "Store PO #{$po->po_number} for {$po->store->store_name} was approved {$hoursOld} hours ago but not yet dispatched.";

            NotificationService::sendToAdmins(
                $title,
                $message,
                'danger',
                route('warehouse.store-orders.show', $po->id)
            );

            NotificationLog::record(
                notificationFor: NotificationLog::FOR_LATE_ORDER,
                message: $message,
                type: NotificationLog::TYPE_IN_APP,
                recipient: 'system',
                relatedId: $po->id
            );

            // Send Email Alert
            $adminEmail = env('WAREHOUSE_EMAIL', 'admin@southwestfarmers.com');
            try {
                Mail::to($adminEmail)->send(new OrderLateAlert($po));
                $this->line("     📩 Email alert sent to {$adminEmail}");
            } catch (\Exception $e) {
                Log::error('[StoreOrderAlerts] Email failed: ' . $e->getMessage());
            }

            $this->line("  → Escalated for PO #{$po->po_number} (approved {$hoursOld}h ago)");
        }
    }
}
