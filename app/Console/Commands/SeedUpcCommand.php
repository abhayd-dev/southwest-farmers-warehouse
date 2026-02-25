<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class SeedUpcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:seed-upc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a UPC code for existing products that do not have one.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::whereNull('upc')->get();

        if ($products->isEmpty()) {
            $this->info('All products already have a UPC code.');
            return;
        }

        $this->withProgressBar($products, function ($product) {
            do {
                $upc = mt_rand(100000000000, 999999999999);
            } while (Product::where('upc', $upc)->exists());

            $product->update([
                'upc' => $upc
            ]);

            if ($product->option) {
                $product->option->update(['upc' => $upc]);
            }
        });

        $this->newLine();
        $this->info('Successfully seeded UPC codes for ' . $products->count() . ' products.');
    }
}
