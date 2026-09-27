<?php

namespace App\Http\Controllers\Warehouse\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Product;
use App\Models\ProductMarketPrice;
use App\Support\DisplayDay;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    public function index(Request $request)
    {
        $markets = Market::active()->get();
        $selectedMarket = $request->market_id ? Market::find($request->market_id) : null;

        $products = collect();
        $activePromotions = collect();

        if ($selectedMarket) {
            $query = Product::whereNull('store_id')->where('is_active', true)
                ->with(['marketPrices' => function ($q) use ($selectedMarket) {
                    $q->where('market_id', $selectedMarket->id);
                }]);

            $search = trim((string) $request->input('search', ''));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'ilike', '%' . $search . '%')
                        ->orWhere('upc', 'ilike', '%' . $search . '%')
                        ->orWhere('barcode', 'ilike', '%' . $search . '%');
                });
            }

            if ($request->filled('last_updated')) {
                $date = $this->parseFilterDate($request->last_updated);
                if ($date === null) {
                    session()->flash('warning', 'Last Updated Date "' . $request->last_updated . '" is not a valid date, so it was not applied.');
                } else {
                    // The day as staff mean it (Chicago), not the UTC date, which rolls over at 7pm.
                    $query->whereHas('marketPrices', function ($q) use ($selectedMarket, $date) {
                        $q->where('market_id', $selectedMarket->id)
                            ->whereBetween('updated_at', [DisplayDay::start($date), DisplayDay::end($date)]);
                    });
                }
            }

            // A fixed order, so page 2 of a filtered list never repeats or skips products.
            $products = $query->orderBy('product_name')->orderBy('id')->paginate(30)->appends($request->query());
        } else {
            // When no specific market is selected for pricing, show all active promotions by default
            $promoQuery = ProductMarketPrice::with(['product', 'market'])
                ->where('promotion_price', '>', 0)
                ->whereDate('promotion_end_date', '>=', now());

            if ($request->filled('promo_market_id')) {
                $promoQuery->where('market_id', $request->promo_market_id);
            }
            if ($request->filled('promo_date')) {
                $date = $this->parseFilterDate($request->promo_date);
                if ($date === null) {
                    session()->flash('warning', 'Filter by Day "' . $request->promo_date . '" is not a valid date, so it was not applied.');
                } else {
                    $promoQuery->whereDate('promotion_start_date', '<=', $date)
                               ->whereDate('promotion_end_date', '>=', $date);
                }
            }

            $activePromotions = $promoQuery->orderBy('promotion_start_date')->orderBy('id')->paginate(30)->appends($request->query());
        }

        return view('warehouse.market-prices.index', compact('markets', 'selectedMarket', 'products', 'activePromotions'));
    }

    /** A filter date as Y-m-d; accepts 2026-09-26 and 09/26/2026. Null when it isn't a real date. */
    private function parseFilterDate(string $value): ?string
    {
        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, trim($value));
            if ($date && $date->format($format) === trim($value)) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    public function update(Request $request)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id',
            'prices' => 'required|array',
        ]);

        $marketId = $request->market_id;

        foreach ($request->prices as $productId => $priceData) {
            if (
                isset($priceData['cost_price']) && isset($priceData['sale_price']) &&
                $priceData['cost_price'] !== '' && $priceData['sale_price'] !== ''
            ) {

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
