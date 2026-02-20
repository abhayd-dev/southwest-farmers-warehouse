<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RevertExpiredPromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:revert-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverts prices for promotions that have reached their end date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $productUpdates = \App\Models\Product::where('promotion_end_date', '<=', $now)
            ->update([
                'promotion_price' => null,
                'promotion_start_date' => null,
                'promotion_end_date' => null,
            ]);

        $marketPriceUpdates = \App\Models\ProductMarketPrice::where('promotion_end_date', '<=', $now)
            ->update([
                'promotion_price' => null,
                'promotion_start_date' => null,
                'promotion_end_date' => null,
            ]);

        $this->info("Expired promotions reverted. Products updated: {$productUpdates}. Market Prices updated: {$marketPriceUpdates}.");
    }
}
