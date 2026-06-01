<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateProductData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all product-related tables safely by disabling foreign key constraints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('WARNING: This will permanently delete ALL product data, stock, sales, and related records. Do you wish to continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $tables = [
            'products',
            'product_categories',
            'product_subcategories',
            'product_options',
            'product_batches',
            'product_stocks',
            'product_min_max_levels',
            'product_ingredients',
            'import_tasks',
            'store_stocks',
            'stock_transactions',
            'stock_requests',
            'stock_transfers',
            'stock_adjustments',
            'stock_audits',
            'stock_audit_items',
            'purchase_orders',
            'purchase_order_items',
            'carts',
            'cart_items',
            'sales',
            'sale_items',
            'sale_returns',
            'sale_return_items',
            'promotions',
            'price_history',
            'market_prices',
            'free_weight_packages',
            'pallets',
        ];

        Schema::disableForeignKeyConstraints();

        $this->info('Truncating tables...');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info("✓ Truncated: $table");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('All product-related data has been successfully cleared.');
    }
}
