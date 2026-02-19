<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PriceHistory;

class PricingService
{
    /**
     * Track price change in history
     */
    public function trackPriceChange($productId, $oldPrice, $newPrice, $userId, $reason = null, $changeType = 'manual')
    {
        // Calculate margins if available
        $product = Product::find($productId);
        $oldMargin = $product && $product->cost_price > 0 
            ? (($oldPrice - $product->cost_price) / $product->cost_price) * 100 
            : null;
        $newMargin = $product && $product->cost_price > 0 
            ? (($newPrice - $product->cost_price) / $product->cost_price) * 100 
            : null;

        return PriceHistory::create([
            'product_id' => $productId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'old_margin' => $oldMargin,
            'new_margin' => $newMargin,
            'changed_by' => $userId,
            'changed_at' => now(),
            'reason' => $reason,
            'change_type' => $changeType,
        ]);
    }

    /**
     * Round retail price to end in .9
     */
    public function roundRetailPrice($price)
    {
        // Round to nearest dollar, then subtract 0.1 to make it end in .9
        $rounded = ceil($price);
        return $rounded - 0.1;
    }

    /**
     * Apply market-level pricing to a product
     */
    public function applyMarketPricing($productId, $marketId, $costPrice, $salePrice, $marginPercent)
    {
        return \App\Models\ProductMarketPrice::updateOrCreate(
            [
                'product_id' => $productId,
                'market_id' => $marketId,
            ],
            [
                'cost_price' => $costPrice,
                'sale_price' => $this->roundRetailPrice($salePrice),
                'margin_percent' => $marginPercent,
            ]
        );
    }

    /**
     * Apply store-specific pricing (overrides market pricing)
     */
    public function applyStorePricing($productId, $storeId, $costPrice, $salePrice, $marginPercent)
    {
        return \App\Models\StoreProductPrice::updateOrCreate(
            [
                'product_id' => $productId,
                'store_id' => $storeId,
            ],
            [
                'cost_price' => $costPrice,
                'sale_price' => $this->roundRetailPrice($salePrice),
                'margin_percent' => $marginPercent,
            ]
        );
    }

    /**
     * Apply promotion with date range
     */
    public function applyPromotion($productId, $newPrice, $startDate, $endDate, $userId)
    {
        $product = Product::find($productId);
        $originalPrice = $product->sale_price;

        // Track price change
        $this->trackPriceChange($productId, $originalPrice, $newPrice, $userId, 'Promotion', 'promotion');

        // Update product price
        $product->update(['sale_price' => $newPrice]);

        // Create or update promotion record
        return \App\Models\Promotion::updateOrCreate(
            ['product_id' => $productId],
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'original_price' => $originalPrice,
                'auto_revert' => true,
            ]
        );
    }

    /**
     * Revert promotion (called by scheduled job)
     */
    public function revertPromotion($promotionId, $userId)
    {
        $promotion = \App\Models\Promotion::find($promotionId);
        
        if (!$promotion || !$promotion->auto_revert) {
            return false;
        }

        $product = Product::find($promotion->product_id);
        $currentPrice = $product->sale_price;

        // Revert to original price
        $product->update(['sale_price' => $promotion->original_price]);

        // Track price change
        $this->trackPriceChange(
            $promotion->product_id,
            $currentPrice,
            $promotion->original_price,
            $userId,
            'Promotion ended - auto-reverted',
            'promotion_revert'
        );

        // Mark promotion as inactive or delete
        $promotion->delete();

        return true;
    }
}
