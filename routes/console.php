<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::command('po:check-delays')->hourly();

// Item 16: remind the external approver every 30 minutes while a PO sits unapproved.
Schedule::command('po:remind-approvers')->everyThirtyMinutes();

Schedule::command('warehouse:automation')->dailyAt('09:00');

// Phase 7: Store Order Automation (Replenishment)
Schedule::command('store-orders:check-alerts')->hourly();
Schedule::command('warehouse:generate-store-pos')->dailyAt('07:00');
Schedule::command('store-orders:notify-warehouse-manager')->hourly();

// Phase 7: Promotions Auto-Revert
Schedule::command('promotions:revert-expired')->hourly();
