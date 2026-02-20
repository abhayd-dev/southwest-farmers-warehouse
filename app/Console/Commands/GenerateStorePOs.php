<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoPOGenerationService;
use Illuminate\Support\Facades\Log;

class GenerateStorePOs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:generate-store-pos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Purchase Orders for stores based on their stock levels and schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Auto-PO Generation...');
        Log::info('[Command] warehouse:generate-store-pos started.');

        try {
            $createdPOs = AutoPOGenerationService::generateForAllStores();
            
            $count = count($createdPOs);
            $this->info("Generated {$count} Purchase Orders.");
            Log::info("[Command] warehouse:generate-store-pos completed. Generated: {$count}");
            
            foreach ($createdPOs as $po) {
                $this->line(" - PO #{$po->po_number} for Store #{$po->store_id}");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("[Command] warehouse:generate-store-pos failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
