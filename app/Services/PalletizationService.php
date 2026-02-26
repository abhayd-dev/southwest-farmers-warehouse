<?php

namespace App\Services;

use App\Models\Pallet;
use App\Models\StockTransfer;
use App\Models\Product;

class PalletizationService
{
    const MAX_PALLET_WEIGHT = 2200; // lbs

    /**
     * Auto-calculate pallets for a stock transfer
     */
    public function calculatePallets(StockTransfer $transfer)
    {
        $items = $transfer->items()->with('product')->get();

        // Group items by department
        $itemsByDepartment = $items->groupBy(function ($item) {
            return $item->product->department_id ?? 'general';
        });

        $pallets = [];

        foreach ($itemsByDepartment as $departmentId => $departmentItems) {
            $currentPallet = null;

            foreach ($departmentItems as $item) {
                $product = $item->product;
                $itemWeight = ($product->weight ?? 0) * $item->quantity;

                // If no current pallet or adding this item would exceed max weight
                if (!$currentPallet || ($currentPallet->total_weight + $itemWeight) > self::MAX_PALLET_WEIGHT) {
                    // Create new pallet
                    $currentPallet = Pallet::create([
                        'transfer_id' => $transfer->id,
                        'pallet_number' => Pallet::generatePalletNumber(),
                        'department_id' => $departmentId === 'general' ? null : $departmentId,
                        'total_weight' => 0,
                        'max_weight' => self::MAX_PALLET_WEIGHT,
                        'status' => Pallet::STATUS_PREPARING,
                    ]);

                    $pallets[] = $currentPallet;
                }

                // Add item to current pallet
                try {
                    $currentPallet->addItem(
                        $product->id,
                        $item->quantity,
                        $product->weight ?? 0
                    );
                } catch (\Exception $e) {
                    // If it fails, create a new pallet and try again
                    $currentPallet = Pallet::create([
                        'transfer_id' => $transfer->id,
                        'pallet_number' => Pallet::generatePalletNumber(),
                        'department_id' => $departmentId === 'general' ? null : $departmentId,
                        'total_weight' => 0,
                        'max_weight' => self::MAX_PALLET_WEIGHT,
                        'status' => Pallet::STATUS_PREPARING,
                    ]);

                    $pallets[] = $currentPallet;

                    $currentPallet->addItem(
                        $product->id,
                        $item->quantity,
                        $product->weight ?? 0
                    );
                }
            }
        }

        return $pallets;
    }

    /**
     * Validate pallet weights
     */
    public function validatePallets($transferId)
    {
        $pallets = Pallet::where('transfer_id', $transferId)->get();
        $overweightPallets = [];

        foreach ($pallets as $pallet) {
            if ($pallet->isOverweight()) {
                $overweightPallets[] = [
                    'pallet_number' => $pallet->pallet_number,
                    'total_weight' => $pallet->total_weight,
                    'max_weight' => $pallet->max_weight,
                    'excess' => $pallet->total_weight - $pallet->max_weight,
                ];
            }
        }

        return [
            'valid' => empty($overweightPallets),
            'overweight_pallets' => $overweightPallets,
        ];
    }

    /**
     * Rearrange pallets manually
     */
    public function rearrangePallets($transferId, array $palletArrangements)
    {
        // Delete existing pallets
        Pallet::where('transfer_id', $transferId)->delete();

        // Create new pallets based on arrangements
        foreach ($palletArrangements as $arrangement) {
            $pallet = Pallet::create([
                'transfer_id' => $transferId,
                'pallet_number' => Pallet::generatePalletNumber(),
                'department_id' => $arrangement['department_id'] ?? null,
                'total_weight' => 0,
                'max_weight' => self::MAX_PALLET_WEIGHT,
                'status' => Pallet::STATUS_PREPARING,
            ]);

            foreach ($arrangement['items'] as $item) {
                $pallet->addItem(
                    $item['product_id'],
                    $item['quantity'],
                    $item['weight_per_unit']
                );
            }
        }

        return $this->validatePallets($transferId);
    }

    /**
     * Calculate optimal pallet arrangement based on weight, dimensions, and rules.
     * Can be used for Store POs or Stock Transfers.
     */
    public function calculateOptimalArrangement($items)
    {
        $maxWeight = config('warehouse.pallet.max_weight_lbs', self::MAX_PALLET_WEIGHT);
        $maxHeight = config('warehouse.pallet.max_height_in', 60);

        // Step 1: Pre-process items into individual cartons
        $cartonsToPack = [];

        foreach ($items as $item) {
            $product = $item->product;

            // Calculate cartons. E.g. 50 units ordered / 10 units per carton = 5 cartons.
            $unitsPerCarton = max(1, $product->units_per_carton ?? 1);
            $numberOfCartons = ceil($item->quantity / $unitsPerCarton);

            // Weight per carton
            $cartonWeight = ($product->weight ?? 0) * $unitsPerCarton;

            for ($i = 0; $i < $numberOfCartons; $i++) {
                $cartonsToPack[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'is_fragile' => $product->is_fragile ?? false,
                    'is_stackable' => $product->is_stackable ?? true,
                    'weight' => $cartonWeight,
                    'height' => $product->carton_height ?? 10, // default 10in if not set
                    'units_in_carton' => ($i == $numberOfCartons - 1) ?
                        ($item->quantity - ($i * $unitsPerCarton)) : $unitsPerCarton, // handle remainder
                ];
            }
        }

        // Step 2 & 3: Sorting for Space Optimization
        // 1. Heavy Non-fragile first (bottom)
        // 2. Lighter non-fragile
        // 3. Stackable Fragile
        // 4. Non-stackable (goes on absolute top layer)
        usort($cartonsToPack, function ($a, $b) {
            // Non-stackable always goes last (top)
            if (!$a['is_stackable'] && $b['is_stackable']) return 1;
            if ($a['is_stackable'] && !$b['is_stackable']) return -1;

            // Fragile goes after non-fragile
            if ($a['is_fragile'] !== $b['is_fragile']) {
                return $a['is_fragile'] ? 1 : -1;
            }

            // Heavy goes first (bottom)
            return $b['weight'] <=> $a['weight'];
        });

        // Step 4: Distribution Algorithm
        $palletsWrapper = [];
        $currentPallet = [
            'total_weight' => 0,
            'current_height' => 0, // Simplified height tracking (assuming 1 layer column for estimation)
            'items' => [] // array of aggregated PO items
        ];

        foreach ($cartonsToPack as $carton) {
            // Check constraints
            $exceedsWeight = ($currentPallet['total_weight'] + $carton['weight']) > $maxWeight;
            $exceedsHeight = ($currentPallet['current_height'] + $carton['height']) > $maxHeight;

            // If the last carton was non-stackable, we can't stack anything else on this "pallet estimation column"
            $lastCartonWasNonStackable = false;
            if (count($currentPallet['items']) > 0) {
                /** @var array $lastItem */
                $lastItem = end($currentPallet['items']);
                if (isset($lastItem['_meta_stackable']) && $lastItem['_meta_stackable'] == false) {
                    $lastCartonWasNonStackable = true;
                }
            }

            if ($exceedsWeight || $exceedsHeight || $lastCartonWasNonStackable) {
                // Seal current pallet, start a new one
                $palletsWrapper[] = $currentPallet;
                $currentPallet = [
                    'total_weight' => 0,
                    'current_height' => 0,
                    'items' => []
                ];
            }

            // Add carton to current pallet
            $currentPallet['total_weight'] += $carton['weight'];
            $currentPallet['current_height'] += $carton['height'];

            // Aggregate identical products back together for the output
            if (!isset($currentPallet['items'][$carton['product_id']])) {
                $currentPallet['items'][$carton['product_id']] = [
                    'product_id' => $carton['product_id'],
                    'product_name' => $carton['product_name'],
                    'total_quantity' => 0,
                    'total_cartons' => 0,
                    'weight_per_unit' => $carton['weight'] / max(1, $carton['units_in_carton']),
                    '_meta_stackable' => $carton['is_stackable']
                ];
            }

            $currentPallet['items'][$carton['product_id']]['total_quantity'] += $carton['units_in_carton'];
            $currentPallet['items'][$carton['product_id']]['total_cartons'] += 1;
        }

        // Add the last pallet if not empty
        if (count($currentPallet['items']) > 0) {
            $palletsWrapper[] = $currentPallet;
        }

        // Format output
        $formattedPallets = [];
        foreach ($palletsWrapper as $index => $p) {
            $formattedItems = [];
            foreach ($p['items'] as $item) {
                unset($item['_meta_stackable']);
                $formattedItems[] = $item;
            }

            $formattedPallets[] = [
                'pallet_number' => 'EST-PLT-' . ($index + 1),
                'total_weight' => round($p['total_weight'], 2),
                'max_weight' => $maxWeight,
                'weight_percent' => min(100, round(($p['total_weight'] / $maxWeight) * 100)),
                'items' => $formattedItems
            ];
        }

        return $formattedPallets;
    }
}
