<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StoreStock;
use App\Models\PurchaseOrder; // Added
use App\Models\WareSetting;
use App\Mail\LowStockAlert;
use App\Mail\LateDeliveryAlert; // Added
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RunWarehouseAutomation extends Command
{
    protected $signature = 'warehouse:automation';
    protected $description = 'Check stock levels and PO status to send alerts';

    public function handle()
    {
        $this->info('Starting Warehouse Automation...');

        // 1. Get Settings
        $emailString = WareSetting::get_value('alert_emails', '');
        
        if (empty($emailString)) {
            $this->error('No email addresses configured in Settings.');
            return;
        }
        $emails = array_filter(explode(',', $emailString));

        // 2. Check Low Stock
        if (WareSetting::get_value('enable_low_stock_email', 0)) {
            $this->checkLowStock($emails);
        }

        // 3. Check Late Deliveries (NEW)
        if (WareSetting::get_value('enable_late_po_email', 0)) {
            $this->checkLateDeliveries($emails);
        }

        $this->info('Automation Run Complete.');
    }

    private function checkLowStock($recipients)
    {
        $threshold = (int) WareSetting::get_value('low_stock_threshold', 10);
        $this->info("Checking Low Stock (< $threshold)...");

        $products = Product::whereNull('store_id')
            ->where('is_active', true)
            ->select('id', 'product_name', 'sku', 'unit')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1),
                'store_qty' => StoreStock::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ])
            ->get()
            ->filter(function ($product) use ($threshold) {
                $product->total_stock = ((int)$product->warehouse_qty) + ((int)$product->store_qty);
                return $product->total_stock < $threshold;
            });

        if ($products->count() > 0) {
            try {
                Mail::to($recipients)->send(new LowStockAlert($products));
                $this->info("Sent Low Stock Alert for {$products->count()} items.");
            } catch (\Exception $e) {
                Log::error('Low Stock Mail Error: ' . $e->getMessage());
            }
        }
    }

    // --- NEW FUNCTION ---
    private function checkLateDeliveries($recipients)
    {
        $this->info("Checking Late Deliveries...");

        // Find POs that are NOT received/cancelled AND Expected Date has passed
        $latePOs = PurchaseOrder::with('vendor')
            ->whereNotIn('status', ['received', 'cancelled', 'completed'])
            ->whereDate('expected_delivery_date', '<', Carbon::today())
            ->get();

        if ($latePOs->count() > 0) {
            try {
                Mail::to($recipients)->send(new LateDeliveryAlert($latePOs));
                $this->info("Sent Late Delivery Alert for {$latePOs->count()} POs.");
            } catch (\Exception $e) {
                Log::error('Late Delivery Mail Error: ' . $e->getMessage());
            }
        } else {
            $this->info("No late deliveries found.");
        }
    }
}