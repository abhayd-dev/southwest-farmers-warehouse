<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\StorePurchaseOrder;
use App\Services\WarehouseManagerNotificationService;
use Illuminate\Console\Command;

/**
 * Safety-net for incoming store orders that were NOT created through
 * AutoPOGenerationService::generateForStore() in this codebase — e.g. a
 * store manually creating its own PO in the separate store-side app,
 * which writes directly to the shared `store_purchase_orders` table and
 * so never fires this app's in-process notification call.
 *
 * This polls the shared table for pending orders that have no "sent"
 * notification log yet, so every incoming order reaches the Warehouse
 * Manager regardless of which app created it.
 */
class NotifyWarehouseManagerOfIncomingOrders extends Command
{
    protected $signature = 'store-orders:notify-warehouse-manager';
    protected $description = 'Email Warehouse Manager(s) about any pending store orders not yet notified';

    public function handle(WarehouseManagerNotificationService $service): int
    {
        $alreadyNotifiedIds = NotificationLog::where('notification_for', NotificationLog::FOR_WAREHOUSE_MANAGER_INCOMING_ORDER)
            ->where('status', NotificationLog::STATUS_SENT)
            ->pluck('related_id');

        $pending = StorePurchaseOrder::where('status', StorePurchaseOrder::STATUS_PENDING)
            ->whereNotIn('id', $alreadyNotifiedIds)
            ->with(['store', 'items.product'])
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No un-notified pending store orders found.');
            return self::SUCCESS;
        }

        $this->info("Found {$pending->count()} pending store order(s) to notify Warehouse Manager about.");

        foreach ($pending as $po) {
            $sent = $service->notifyIncomingOrder($po);
            $this->line(($sent ? '  ✅ ' : '  ⏭️  ') . "PO #{$po->po_number}");
        }

        return self::SUCCESS;
    }
}
