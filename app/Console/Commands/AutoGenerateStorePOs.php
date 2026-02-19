<?php

namespace App\Console\Commands;

use App\Models\StoreDetail;
use App\Models\StoreOrderSchedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreOrderCreated;
use App\Services\AutoPOGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoGenerateStorePOs extends Command
{
    protected $signature   = 'store-orders:auto-generate {--store= : Specific store ID to generate for}';
    protected $description = 'Auto-generate store purchase orders based on min/max stock levels';

    public function handle(): int
    {
        $storeId = $this->option('store');

        if ($storeId) {
            // Single store mode
            $store = StoreDetail::find($storeId);
            if (!$store) {
                $this->error("Store #{$storeId} not found.");
                return self::FAILURE;
            }

            $this->info("Generating PO for store: {$store->store_name}...");
            $po = AutoPOGenerationService::generateForStore($store);

            if ($po) {
                $this->info("✅ Created PO #{$po->po_number} with {$po->items->count()} items.");
            } else {
                $this->line("ℹ️  No PO needed for {$store->store_name} (stock sufficient or duplicates exist).");
            }

            return self::SUCCESS;
        }

        // All stores mode
        $this->info('Running auto-PO generation for all active stores...');

        $stores = StoreDetail::where('is_active', true)->get();
        $this->info("Found {$stores->count()} active stores.");

        $created = [];
        $skipped = 0;

        $todayDay = Carbon::now()->format('l');

        foreach ($stores as $store) {
            // Check Schedule
            $schedule = StoreOrderSchedule::where('store_id', $store->id)
                ->where('is_active', true)
                ->first();

            if (!$schedule) {
                $this->line("  ⏭️  {$store->store_name}: Skipped (No active schedule).");
                $skipped++;
                continue;
            }

            if (strcasecmp($schedule->expected_day, $todayDay) !== 0) {
                $this->line("  ⏭️  {$store->store_name}: Skipped (Scheduled: {$schedule->expected_day}).");
                $skipped++;
                continue;
            }

            try {
                $po = AutoPOGenerationService::generateForStore($store);
                if ($po) {
                    $this->line("  ✅ {$store->store_name}: PO #{$po->po_number} created.");
                    $created[] = $po;

                    // Send Email
                    if ($store->email) {
                        try {
                            Mail::to($store->email)->send(new StoreOrderCreated($po));
                            $this->line("     📩 Email sent to {$store->email}");
                        } catch (\Exception $e) {
                            $this->error("     ❌ Failed to send email: " . $e->getMessage());
                        }
                    }
                } else {
                    $skipped++;
                    $this->line("  ⏭️  {$store->store_name}: No PO needed.");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$store->store_name}: Failed — {$e->getMessage()}");
                Log::error("[AutoGenerateStorePOs] Store #{$store->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. Created: " . count($created) . " POs | Skipped: {$skipped} stores.");

        return self::SUCCESS;
    }
}
