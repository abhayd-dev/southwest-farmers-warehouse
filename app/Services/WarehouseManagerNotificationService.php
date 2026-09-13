<?php

namespace App\Services;

use App\Mail\IncomingStoreOrderNotification;
use App\Models\NotificationLog;
use App\Models\StorePurchaseOrder;
use App\Models\WareUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WarehouseManagerNotificationService
{
    /**
     * Email every active "Warehouse Manager" the incoming store order,
     * with a PDF of the order details attached. Safe to call more than
     * once for the same order — it will not send a duplicate.
     */
    public function notifyIncomingOrder(StorePurchaseOrder $storeOrder): bool
    {
        if ($this->alreadyNotified($storeOrder)) {
            return false;
        }

        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            Log::warning("[WarehouseManagerNotification] No active 'Warehouse Manager' with an email found — order #{$storeOrder->po_number} not sent.");
            return false;
        }

        $anySent = false;

        foreach ($recipients as $manager) {
            try {
                Mail::to($manager->email)->send(new IncomingStoreOrderNotification($storeOrder));
                $anySent = true;

                NotificationLog::record(
                    notificationFor: NotificationLog::FOR_WAREHOUSE_MANAGER_INCOMING_ORDER,
                    message: "Incoming order #{$storeOrder->po_number} emailed to Warehouse Manager {$manager->name}",
                    type: NotificationLog::TYPE_EMAIL,
                    recipient: $manager->email,
                    relatedId: $storeOrder->id
                );
            } catch (\Throwable $e) {
                Log::error("[WarehouseManagerNotification] Failed to email {$manager->email} for order #{$storeOrder->po_number}: " . $e->getMessage());

                NotificationLog::record(
                    notificationFor: NotificationLog::FOR_WAREHOUSE_MANAGER_INCOMING_ORDER,
                    message: "Failed to email Warehouse Manager {$manager->name}: " . $e->getMessage(),
                    type: NotificationLog::TYPE_EMAIL,
                    recipient: $manager->email,
                    relatedId: $storeOrder->id,
                    status: NotificationLog::STATUS_FAILED
                );
            }
        }

        return $anySent;
    }

    protected function recipients()
    {
        return WareUser::whereHas('roles', fn ($q) => $q->where('name', 'Warehouse Manager'))
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();
    }

    /**
     * A "sent" log (not "failed") for this order already exists.
     */
    protected function alreadyNotified(StorePurchaseOrder $storeOrder): bool
    {
        return NotificationLog::where('notification_for', NotificationLog::FOR_WAREHOUSE_MANAGER_INCOMING_ORDER)
            ->where('related_id', $storeOrder->id)
            ->where('status', NotificationLog::STATUS_SENT)
            ->exists();
    }
}
