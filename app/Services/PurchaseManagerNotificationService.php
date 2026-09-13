<?php

namespace App\Services;

use App\Mail\ShortageReportNotification;
use App\Models\NotificationLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\WareUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PurchaseManagerNotificationService
{
    /**
     * Email every active "Purchase Manager" a shortage report (with PDF)
     * for the given short-received PurchaseOrderItem ids on this PO.
     * Safe to call more than once for the same receiving event — logged
     * per PO + item set, so a resend attempt (e.g. from a retry) does not
     * spam a manager who already got this exact shortfall.
     */
    public function notifyShortage(PurchaseOrder $po, array $shortageItemIds): bool
    {
        $items = PurchaseOrderItem::with('product')
            ->whereIn('id', $shortageItemIds)
            ->get();

        if ($items->isEmpty()) {
            return false;
        }

        $dedupKey = $this->dedupKey($po, $items);

        if ($this->alreadyNotified($po, $dedupKey)) {
            return false;
        }

        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            Log::warning("[PurchaseManagerNotification] No active 'Purchase Manager' with an email found — shortage on PO #{$po->po_number} not sent.");
            return false;
        }

        $anySent = false;

        foreach ($recipients as $manager) {
            try {
                Mail::to($manager->email)->send(new ShortageReportNotification($po, $items));
                $anySent = true;

                NotificationLog::record(
                    notificationFor: NotificationLog::FOR_PURCHASE_MANAGER_SHORTAGE,
                    message: "Shortage on PO #{$po->po_number} ({$dedupKey}) emailed to Purchase Manager {$manager->name}",
                    type: NotificationLog::TYPE_EMAIL,
                    recipient: $manager->email,
                    relatedId: $po->id
                );
            } catch (\Throwable $e) {
                Log::error("[PurchaseManagerNotification] Failed to email {$manager->email} for PO #{$po->po_number}: " . $e->getMessage());

                NotificationLog::record(
                    notificationFor: NotificationLog::FOR_PURCHASE_MANAGER_SHORTAGE,
                    message: "Failed to email Purchase Manager {$manager->name} ({$dedupKey}): " . $e->getMessage(),
                    type: NotificationLog::TYPE_EMAIL,
                    recipient: $manager->email,
                    relatedId: $po->id,
                    status: NotificationLog::STATUS_FAILED
                );
            }
        }

        return $anySent;
    }

    protected function recipients()
    {
        return WareUser::whereHas('roles', fn ($q) => $q->where('name', 'Purchase Manager'))
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();
    }

    /**
     * A receiving action can happen more than once against the same PO
     * (partial receipts). Each distinct set of short item ids + their
     * received quantities is treated as its own shortage event, so a
     * second, later short-receive on the same PO still notifies.
     */
    protected function dedupKey(PurchaseOrder $po, $items): string
    {
        return $items->map(fn ($i) => "{$i->id}:{$i->received_quantity}")->sort()->implode(',');
    }

    protected function alreadyNotified(PurchaseOrder $po, string $dedupKey): bool
    {
        return NotificationLog::where('notification_for', NotificationLog::FOR_PURCHASE_MANAGER_SHORTAGE)
            ->where('related_id', $po->id)
            ->where('status', NotificationLog::STATUS_SENT)
            ->where('message', 'like', "%({$dedupKey})%")
            ->exists();
    }
}
