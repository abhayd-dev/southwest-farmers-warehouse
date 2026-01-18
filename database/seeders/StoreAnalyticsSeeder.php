<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;

class StoreAnalyticsSeeder extends Seeder
{
    public function run()
    {
        $storeId = 1; // Target Store
        $products = Product::limit(5)->get(); // Get some products

        if ($products->isEmpty()) {
            $this->command->info('No products found. Please seed products first.');
            return;
        }

        // Generate sales data for the last 7 days
        for ($i = 0; $i < 50; $i++) {
            $product = $products->random();
            $date = Carbon::now()->subDays(rand(0, 7)); // Random date within last week
            $qty = rand(1, 10); // Random quantity sold

            DB::table('stock_transactions')->insert([
                'product_id' => $product->id,
                'warehouse_id' => 1, // Default Warehouse
                'store_id' => $storeId, // REQUIRED: This fixes your error
                'product_batch_id' => null, // Optional for this test
                'ware_user_id' => 1, // Assuming admin user exists
                'type' => 'sale_out', // This type matches your analytics query
                'quantity_change' => -$qty, // Negative for sales/out
                'running_balance' => rand(100, 500),
                'reference_id' => 'SALE-' . rand(1000, 9999),
                'remarks' => 'Test Sale for Analytics',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->command->info('Store Analytics Data Seeded Successfully!');
    }
}