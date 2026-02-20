<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StoreDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class PosSyncController extends Controller
{
    /**
     * Syncs products for a specific store, applying market pricing if applicable.
     */
    public function syncProducts($storeCode)
    {
        $store = StoreDetail::with('markets')->where('store_code', $storeCode)->firstOrFail();
        $market = $store->markets->first();

        $productsQuery = Product::whereNull('store_id')->where('is_active', true);
        
        if ($market) {
            $productsQuery->with(['marketPrices' => function($q) use ($market) {
                $q->where('market_id', $market->id);
            }]);
        }

        $products = $productsQuery->get()->map(function($product) use ($market) {
            $costPrice = $product->cost_price;
            $salePrice = $product->price;

            // Apply Market Price override if it exists
            if ($market && $product->marketPrices->isNotEmpty()) {
                $marketPrice = $product->marketPrices->first();
                $costPrice = $marketPrice->cost_price;
                $salePrice = $marketPrice->sale_price;
            }

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->product_name,
                'category_id' => $product->category_id,
                'subcategory_id' => $product->subcategory_id,
                'brand' => $product->brand,
                'unit' => $product->unit,
                'cost_price' => (float) $costPrice,
                'sale_price' => (float) $salePrice,
                'stock_alert_level' => $product->stock_alert_level,
                'is_active' => $product->is_active,
            ];
        });

        return response()->json([
            'status' => 'success',
            'store' => [
                'code' => $store->store_code,
                'name' => $store->store_name,
                'market' => $market ? $market->name : 'N/A',
            ],
            'timestamp' => now()->toISOString(),
            'products_count' => $products->count(),
            'products' => $products
        ]);
    }
}
