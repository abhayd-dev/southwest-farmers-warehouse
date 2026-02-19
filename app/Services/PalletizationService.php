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
}
