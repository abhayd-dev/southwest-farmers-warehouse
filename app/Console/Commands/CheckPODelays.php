<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\WareUser;
use App\Mail\PODelayedMail;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckPODelays extends Command
{
    protected $signature = 'po:check-delays';
    protected $description = 'Check for delayed POs and send escalation emails';

    public function handle()
    {
        $this->info('Checking for delayed Purchase Orders...');

        // Get POs that are NOT completed/cancelled
        $pendingPOs = PurchaseOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('expected_delivery_date')
            ->get();

        foreach ($pendingPOs as $po) {
            // Calculate hours passed since expected delivery date (midnight)
            // Ideally, expected_delivery_date is a Date, so we count from end of that day or specific time.
            // Let's assume deadline was End of Day (23:59:59) of expected date.
            $deadline = Carbon::parse($po->expected_delivery_date)->endOfDay();
            $hoursLate = now()->diffInHours($deadline, false); // Negative if past deadline

            // We need positive hours *after* deadline passed.
            // If hoursLate is -2, it means 2 hours passed since deadline.
            $hoursPassed = abs($hoursLate);

            // Only proceed if deadline has passed (hoursLate is negative)
            if ($hoursLate >= 0) continue;

            // --- LEVEL 1: GM (After 2 Hours) ---
            if ($hoursPassed >= 2 && $po->alert_level < 1) {
                $this->sendAlert($po, 1, ['General Manager']);
            }

            // --- LEVEL 2: GM + RM (After 4 Hours) ---
            else if ($hoursPassed >= 4 && $po->alert_level < 2) {
                $this->sendAlert($po, 2, ['General Manager', 'Regional Manager']);
            }

            // --- LEVEL 3: GM + RM + VP (After 8 Hours) ---
            else if ($hoursPassed >= 8 && $po->alert_level < 3) {
                $this->sendAlert($po, 3, ['General Manager', 'Regional Manager', 'VP Operations']);
            }
        }

        $this->info('Check complete.');
    }

    private function sendAlert($po, $level, $roles)
    {
        // Find users with these roles
        // Note: Ensure your WareRole names match exactly (e.g., 'General Manager')
        $users = WareUser::whereHas('roles', function ($q) use ($roles) {
            $q->whereIn('name', $roles);
        })->get();

        if ($users->isEmpty()) {
            $this->error("No users found for Level $level alert.");
            // We still update alert_level so we don't loop forever
        } else {
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->send(new PODelayedMail($po, $level));
                    $this->info("Email sent to {$user->name} ({$user->email})");
                }
                $hoursPassed = now()->diffInHours(Carbon::parse($po->expected_delivery_date)->endOfDay(), false) * -1;

                NotificationService::sendToAdmins(
                    '🚨 Late Delivery Alert',
                    "PO #{$po->po_number} from {$po->vendor->name} is delayed by {$hoursPassed} hours.",
                    'danger',
                    route('warehouse.purchase-orders.show', $po->id)
                );
            }
        }

        // Update PO tracking
        $po->update(['alert_level' => $level]);
    }
}
