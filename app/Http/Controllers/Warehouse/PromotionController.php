<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\StoreDetail;
use Illuminate\Http\Request;

/**
 * Cross-store, read-focused view of promotions for the Warehouse team.
 * Individual promotions are still created/managed per-store on the Store
 * side — this is oversight/visibility, showing every store by default.
 */
class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with(['store', 'product', 'category'])->latest();

        // Store filter — no store filter applied by default, so every
        // store's promotions show together (the client's explicit ask).
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // Day filter — which promotions are active on a given calendar date.
        if ($request->filled('day')) {
            $query->whereDate('start_date', '<=', $request->day)
                  ->whereDate('end_date', '>=', $request->day);
        }

        $promotions = $query->paginate(20)->withQueryString();
        $stores = StoreDetail::orderBy('store_name')->get();

        return view('warehouse.promotions.index', compact('promotions', 'stores'));
    }
}
