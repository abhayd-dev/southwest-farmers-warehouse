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
}
