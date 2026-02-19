<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::command('po:check-delays')->hourly();

Schedule::command('warehouse:automation')->dailyAt('09:00');

// Phase 3: Store Order Automation
Schedule::command('store-orders:check-alerts')->hourly();
Schedule::command('store-orders:auto-generate')->dailyAt('07:00');

