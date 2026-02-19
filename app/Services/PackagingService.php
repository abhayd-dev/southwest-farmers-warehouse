<?php

namespace App\Services;

use App\Models\FreeWeightPackage;
use App\Models\FreeWeightProduct;
use App\Models\PackagingEvent;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PackagingService
{
    /**
     * Execute a packaging event: Convert Bulk Weight -> Discrete Packages
     */
    public function executePackaging(int $packageId, int $packagesToCreate, ?string $notes = null)
    {
        return DB::transaction(function () use ($packageId, $packagesToCreate, $notes) {
            
            // 1. Load Definition
            $package = FreeWeightPackage::with('freeWeightProduct', 'targetProduct')->findOrFail($packageId);
            $bulkProduct = $package->freeWeightProduct;

            // 2. Calculate Required Bulk Weight
            $requiredWeight = $package->package_size * $packagesToCreate;

            // 3. Check Bulk Availability
            if ($bulkProduct->bulk_weight < $requiredWeight) {
                throw new \Exception("Insufficient Bulk Weight. Required: {$requiredWeight} {$bulkProduct->unit}, Available: {$bulkProduct->bulk_weight} {$bulkProduct->unit}");
            }

            // 4. Deduct Bulk Weight
            $bulkProduct->decrement('bulk_weight', $requiredWeight);

            // 5. Add Stock to Target Product (The 10lb Bag)
            if ($package->target_product_id) {
                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id'   => $package->target_product_id,
                        'warehouse_id' => $bulkProduct->warehouse_id
                    ],
                    ['quantity' => 0]
                );

                $stock->increment('quantity', $packagesToCreate);

                // Log Stock Transaction for the produced item
                StockTransaction::create([
                    'product_id'       => $package->target_product_id,
                    'warehouse_id'     => $bulkProduct->warehouse_id,
                    'ware_user_id'     => Auth::id(),
                    'type'             => 'production', // New transaction type
                    'quantity_change'  => $packagesToCreate,
                    'running_balance'  => $stock->quantity,
                    'remarks'          => "Produced from Bulk Packaging (Event: {$packagesToCreate} x {$package->package_name})"
                ]);
            }

            // 6. Update Package Counter (Total created stats)
            $package->increment('quantity_created', $packagesToCreate);

            // 7. Record the Event
            $event = PackagingEvent::create([
                'free_weight_product_id' => $bulkProduct->id,
                'package_id'             => $package->id,
                'employee_id'            => Auth::id(),
                'bulk_weight_reduced'    => $requiredWeight,
                'packages_created'       => $packagesToCreate,
                'event_date'             => now(),
                'notes'                  => $notes
            ]);

            return $event;
        });
    }
}
