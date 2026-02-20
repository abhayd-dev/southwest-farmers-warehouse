<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Product;
use App\Models\ProductMarketPrice;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    public function index(Request $request)
    {
        $markets = Market::active()->get();
        $selectedMarket = $request->market_id ? Market::find($request->market_id) : null;
        
        $products = collect();
        if ($selectedMarket) {
            $products = Product::whereNull('store_id')->where('is_active', true)
                ->with(['marketPrices' => function($q) use ($selectedMarket) {
                    $q->where('market_id', $selectedMarket->id);
                }])->get();
        }
        
        return view('warehouse.market-prices.index', compact('markets', 'selectedMarket', 'products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id',
            'prices' => 'required|array',
        ]);

        $marketId = $request->market_id;

        foreach ($request->prices as $productId => $priceData) {
            if (isset($priceData['cost_price']) && isset($priceData['sale_price']) && 
                $priceData['cost_price'] !== '' && $priceData['sale_price'] !== '') {
                
                $cost = (float)$priceData['cost_price'];
                $sale = (float)$priceData['sale_price'];
                
                $margin = 0;
                if ($cost > 0) {
                    $margin = (($sale - $cost) / $cost) * 100;
                }
                
                ProductMarketPrice::updateOrCreate(
                    ['product_id' => $productId, 'market_id' => $marketId],
                    [
                        'cost_price' => $cost,
                        'sale_price' => $sale,
                        'margin_percent' => $margin
                    ]
                );
            }
        }

        return back()->with('success', 'Market Prices updated successfully.');
    }

    public function updatePromo(Request $request)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id',
            'product_id' => 'required|exists:products,id',
            'promotion_price' => 'nullable|numeric|min:0',
            'promotion_start_date' => 'nullable|date',
            'promotion_end_date' => 'nullable|date|after_or_equal:promotion_start_date',
        ]);

        $marketPrice = ProductMarketPrice::where('product_id', $request->product_id)
            ->where('market_id', $request->market_id)
            ->first();
            
        if (!$marketPrice) {
            // Need a base price first
            return back()->with('error', 'Please set a base market selling price before applying a promotion.');
        }

        $marketPrice->update([
            'promotion_price' => $request->promotion_price,
            'promotion_start_date' => $request->promotion_start_date,
            'promotion_end_date' => $request->promotion_end_date,
        ]);

        return back()->with('success', 'Promotion configured successfully.');
    }
}
