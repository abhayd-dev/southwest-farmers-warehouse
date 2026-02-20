<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StoreDetail;
use App\Models\SaleItem;
use App\Models\StorePurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard for all reports
     */
    public function index()
    {
        return view('warehouse.reports.index');
    }

    /**
     * Sales Report
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $storeId   = $request->input('store_id');

        $query = Sale::with('store')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $sales = $query->latest()->paginate(20);
        $totalSales = $query->sum('total_amount');
        $totalTransactions = $query->count();

        $stores = StoreDetail::all();

        return view('warehouse.reports.sales', compact('sales', 'stores', 'startDate', 'endDate', 'storeId', 'totalSales', 'totalTransactions'));
    }

    /**
     * Stock Movement Report (Warehouse)
     */
    public function stockMovement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type      = $request->input('type');

        $query = StockTransaction::with(['product', 'user', 'warehouse'])
            ->where('warehouse_id', 1) // Assuming Main Warehouse ID 1
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->latest()->paginate(20);
        
        // Distinct types for filter
        $types = StockTransaction::select('type')->distinct()->pluck('type');

        return view('warehouse.reports.stock_movement', compact('transactions', 'types', 'startDate', 'endDate', 'type'));
    }

    /**
     * Inventory Health (Low Stock & Expiry)
     */
    public function inventoryHealth()
    {
        // Low Stock Items (Warehouse)
        $lowStockItems = Product::lowStock(20)->with('stock')->get();

        // Expiring Batches (Next 30 days)
        $expiringBatches = ProductBatch::with(['product', 'warehouse'])
            ->where('warehouse_id', 1)
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(30)])
            ->orderBy('expiry_date')
            ->get();

        // Expired Batches
        $expiredBatches = ProductBatch::with(['product', 'warehouse'])
            ->where('warehouse_id', 1)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', Carbon::now())
            ->get();

        return view('warehouse.reports.inventory_health', compact('lowStockItems', 'expiringBatches', 'expiredBatches'));
    }

    /**
     * Fast Moving Items
     */
    public function fastMoving(Request $request)
    {
        $days = $request->input('days', 30);
        $limit = 10;

        $startDate = Carbon::now()->subDays($days);

        // Aggregate by Product across all Sales
        $items = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
            ->whereHas('sale', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take($limit)
            ->get();

        return view('warehouse.reports.fast_moving', compact('items', 'days'));
    }

    /**
     * Top Dispatched Report (Weight vs Unit)
     */
    public function topDispatched(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $dispatchedItems = StorePurchaseOrderItem::select('product_id', DB::raw('SUM(dispatched_qty) as total_dispatched'))
            ->where('dispatched_qty', '>', 0)
            ->whereHas('storePurchaseOrder', function($q) use ($startDate) {
                $q->where('updated_at', '>=', $startDate)
                  ->whereIn('status', ['dispatched', 'completed']);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_dispatched')
            ->get();

        $weightBased = $dispatchedItems->filter(function($item) {
            $unit = strtolower($item->product->unit ?? '');
            return in_array($unit, ['kg', 'lbs', 'g', 'oz', 'litre', 'ml']);
        })->take(15);

        $unitBased = $dispatchedItems->filter(function($item) {
            $unit = strtolower($item->product->unit ?? '');
            return !in_array($unit, ['kg', 'lbs', 'g', 'oz', 'litre', 'ml']);
        })->take(15);

        return view('warehouse.reports.top_dispatched', compact('weightBased', 'unitBased', 'days'));
    }

    /**
     * Warehouse Min Report
     */
    public function warehouseMin()
    {
        $lowStockItems = Product::select('products.*')
            ->join('product_min_max_levels', 'products.id', '=', 'product_min_max_levels.product_id')
            ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->whereNull('products.store_id')
            ->where('product_stocks.warehouse_id', 1)
            ->whereRaw('product_stocks.quantity <= product_min_max_levels.min_level')
            ->with(['stock' => function($q) { $q->where('warehouse_id', 1); }, 'category', 'stocks'])
            ->get();
            
        return view('warehouse.reports.warehouse_min', compact('lowStockItems'));
    }

    /**
     * Sales by Price Point Report
     */
    public function salesByPricePoint(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Group by both Product ID and specific Selling Price to see how different price points perform
        $items = SaleItem::select('product_id', 'price', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as revenue'))
            ->whereHas('sale', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->with('product')
            ->groupBy('product_id', 'price')
            ->orderByDesc('total_qty')
            ->get();

        // Group the final collection by Product ID for nice display
        $groupedProducts = $items->groupBy('product_id');

        return view('warehouse.reports.sales_by_price_point', compact('groupedProducts', 'days'));
    }
}
