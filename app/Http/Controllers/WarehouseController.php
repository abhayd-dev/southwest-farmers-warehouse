<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockRequest;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\Vendor;

class WarehouseController extends Controller
{
    public function dashboard(Request $request)
    {
        set_time_limit(120);
        $user = auth()->user();

        // --- 1. Date Filter Logic ---
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();

        $diffDays = $start->diffInDays($end);
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subDays($diffDays);

        $data = [];

        // --- 2. GLOBAL OVERVIEW ---
        if ($user->isSuperAdmin() || $user->hasPermission('view_dashboard')) {
            $data['total_products'] = Product::where('is_active', true)->count();

            $data['low_stock'] = ProductStock::select('product_id')
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) < ?', [10])
                ->count();

            $data['active_vendors'] = Vendor::where('is_active', true)->count();
        }

        // --- 3. FINANCE & VALUATION ---
        if ($user->isSuperAdmin() || $user->hasPermission('view_financial_reports')) {
            $currentValuation = DB::table('product_stocks')
                ->join('products', 'product_stocks.product_id', '=', 'products.id')
                ->sum(DB::raw('product_stocks.quantity * products.cost_price'));

            $data['inventory_value'] = $currentValuation;

            $currentSpend = PurchaseOrder::where('status', 'received')
                ->whereBetween('updated_at', [$start, $end])
                ->sum('total_amount');

            $prevSpend = PurchaseOrder::where('status', 'received')
                ->whereBetween('updated_at', [$prevStart, $prevEnd])
                ->sum('total_amount');

            $data['po_spend'] = $currentSpend;
            $data['po_trend'] = $this->calculateTrend($currentSpend, $prevSpend);
        }

        // --- 4. INVENTORY OPERATIONS (Charts) ---
        if ($user->isSuperAdmin() || $user->hasPermission('view_inventory')) {

            // Area Chart Data
            $movements = StockTransaction::selectRaw('DATE(created_at) as date, type, SUM(ABS(quantity_change)) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date', 'type')
                ->get();

            $data['chart_dates'] = $movements->pluck('date')->unique()->sort()->values();
            $data['chart_in'] = [];
            $data['chart_out'] = [];

            foreach ($data['chart_dates'] as $date) {
                $data['chart_in'][] = $movements->where('date', $date)->whereIn('type', ['purchase', 'return', 'transfer_in'])->sum('total');
                $data['chart_out'][] = $movements->where('date', $date)->whereIn('type', ['sale', 'transfer_out'])->sum('total');
            }

            // Pie Chart Data (Top Products)
            $data['top_products'] = DB::table('stock_transactions')
                ->join('products', 'stock_transactions.product_id', '=', 'products.id')
                ->where('stock_transactions.type', 'transfer_out')
                ->whereBetween('stock_transactions.created_at', [$start, $end])
                ->select('products.product_name', 'products.sku', DB::raw('SUM(ABS(quantity_change)) as qty'))
                ->groupBy('products.product_name', 'products.sku')
                ->orderByDesc('qty')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    // CRITICAL FIX: Ensure numbers are actually numbers (floats), not strings
                    $item->qty = (float) $item->qty;
                    return $item;
                });
        }

        // --- 5. PROCUREMENT ---
        if ($user->isSuperAdmin() || $user->hasPermission('view_po')) {
            $data['pending_po_count'] = PurchaseOrder::where('status', 'pending')->count();
            $data['approved_po_count'] = PurchaseOrder::where('status', 'approved')->count();

            $data['recent_pos'] = PurchaseOrder::with('vendor')
                ->latest()
                ->limit(5)
                ->get();
        }

        // --- 6. FULFILLMENT ---
        if ($user->isSuperAdmin() || $user->hasPermission('view_stores') || $user->hasPermission('approve_store_requests')) {
            $data['pending_requests'] = StockRequest::where('status', 'pending')->count();

            $data['recent_requests'] = StockRequest::with('store')
                ->latest()
                ->limit(6)
                ->get();
        }

        return view('dashboard', compact('data', 'start', 'end'));
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->search) {
            $query->where('warehouse_name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $warehouses = $query->latest()->paginate(10);

        return view('warehouse.index', compact('warehouses'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'warehouse_name' => 'required|string|max:255',
            'code'           => 'required|string|max:50',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        $warehouse->update($request->all());

        return back()->with('success', 'Warehouse updated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $warehouse = Warehouse::findOrFail($request->id);
        $warehouse->update(['is_active' => $request->status]);

        return response()->json([
            'status'  => true,
            'message' => 'Warehouse status updated successfully.',
        ]);
    }
}
