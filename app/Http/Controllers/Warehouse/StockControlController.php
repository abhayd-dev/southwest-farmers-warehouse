<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StoreStock;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\StoreDetail;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class StockControlController extends Controller
{
    // ===== STOCK OVERVIEW =====
    public function overview()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $subcategories = ProductSubcategory::where('is_active', true)->get();
        return view('warehouse.stock-control.overview', compact('categories', 'subcategories'));
    }

    public function overviewData(Request $request)
    {
        $query = Product::query()
            ->whereNull('products.store_id') // FIXED: Specify table to avoid ambiguity
            ->with(['category', 'subcategory'])
            ->select('products.*')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity - damaged_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1),
                'total_stores_qty' => StoreStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ]);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('products.created_at', [
                    Carbon::parse($dates[0])->startOfDay(),
                    Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        if ($request->low_stock) {
            $query->havingRaw('(warehouse_qty + total_stores_qty) < 10');
        }

        return DataTables::of($query)
            ->addColumn('category_name', fn($row) => $row->category->name ?? '-')
            ->addColumn('subcategory_name', fn($row) => $row->subcategory->name ?? '-')
            ->addColumn('total_qty', fn($row) => (int)$row->warehouse_qty + (int)$row->total_stores_qty)
            ->addColumn('value', fn($row) => number_format(((int)$row->warehouse_qty + (int)$row->total_stores_qty) * ($row->cost_price ?? 0), 2))
            ->rawColumns(['category_name', 'subcategory_name'])
            ->make(true);
    }

    // ===== STOCK VALUATION - DASHBOARD =====
    public function valuation(Request $request)
    {
        $stores = StoreDetail::where('is_active', true)->get();
        $categories = ProductCategory::where('is_active', true)->get();
        
        // Get totals
        $warehouseValue = ProductStock::where('warehouse_id', 1)
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('product_stocks.quantity * products.cost_price'));

        $storesValue = StoreStock::join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('store_stocks.quantity * products.cost_price'));

        $totalValue = $warehouseValue + $storesValue;
        $totalQty = ProductStock::where('warehouse_id', 1)->sum('quantity') + StoreStock::sum('quantity');

        return view('warehouse.stock-control.valuation', compact(
            'warehouseValue',
            'storesValue',
            'totalValue',
            'totalQty',
            'stores',
            'categories'
        ));
    }

    public function valuationData(Request $request)
    {
        $query = Product::query()
            ->whereNull('products.store_id') // FIXED: Specify table to avoid ambiguity
            ->select([
                'products.id',
                'products.product_name',
                'products.sku',
                'products.cost_price',
                DB::raw('COALESCE(SUM(product_stocks.quantity), 0) as warehouse_qty'),
                DB::raw('COALESCE(SUM(product_stocks.quantity * products.cost_price), 0) as warehouse_value'),
                DB::raw('COALESCE(SUM(store_stocks.quantity), 0) as stores_qty'),
                DB::raw('COALESCE(SUM(store_stocks.quantity * products.cost_price), 0) as stores_value'),
                DB::raw('(COALESCE(SUM(product_stocks.quantity * products.cost_price), 0) + COALESCE(SUM(store_stocks.quantity * products.cost_price), 0)) as total_value')
            ])
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->leftJoin('store_stocks', 'products.id', '=', 'store_stocks.product_id')
            ->groupBy('products.id', 'products.product_name', 'products.sku', 'products.cost_price');

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->filled('store_id')) {
            // FIXED: Use correct relation name (storeStocks) and qualified column
            $query->whereHas('storeStocks', fn($q) => $q->where('store_stocks.store_id', $request->store_id));
        }

        return DataTables::of($query)
            ->addColumn('warehouse_value_fmt', fn($row) => '₹ ' . number_format($row->warehouse_value, 2))
            ->addColumn('stores_value_fmt', fn($row) => '₹ ' . number_format($row->stores_value, 2))
            ->addColumn('total_value_fmt', fn($row) => '₹ ' . number_format($row->total_value, 2))
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stock-control.valuation.product', $row->id) . '" 
                   class="btn btn-sm btn-outline-primary">
                   <i class="mdi mdi-chart-line"></i> Analytics
                </a>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    // ===== STORE VALUATION =====
    public function storeValuation(Request $request)
    {
        $stores = StoreDetail::where('is_active', true)
            ->with([
                'stocks' => function ($q) {
                    $q->select('store_stocks.*')
                        ->join('products', 'store_stocks.product_id', '=', 'products.id')
                        ->selectRaw('SUM(store_stocks.quantity * products.cost_price) as store_value');
                }
            ])
            ->get();

        return view('warehouse.stock-control.valuation-stores', compact('stores'));
    }

    // ===== STORE ANALYTICS PAGE =====
    public function storeAnalytics(Request $request, StoreDetail $store)
    {
        $storeValue = StoreStock::where('store_stocks.store_id', $store->id) // FIXED: Qualified column
            ->join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('store_stocks.quantity * products.cost_price'));

        $storeQty = StoreStock::where('store_stocks.store_id', $store->id)->sum('quantity'); // FIXED: Qualified column

        $topProducts = StoreStock::where('store_stocks.store_id', $store->id) // FIXED: Qualified column
            ->join('products', 'store_stocks.product_id', '=', 'products.id')
            ->select([
                'products.product_name',
                'products.sku',
                'store_stocks.quantity',
                DB::raw('store_stocks.quantity * products.cost_price as value')
            ])
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        // 30-day trend
        $trend = StockTransaction::where('stock_transactions.store_id', $store->id) // FIXED: Qualified column (good practice)
            ->where('created_at', '>=', Carbon::today()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('warehouse.stock-control.store-analytics', compact(
            'store',
            'storeValue',
            'storeQty',
            'topProducts',
            'trend'
        ));
    }

    // ===== PRODUCT ANALYTICS PAGE =====
    public function productAnalytics(Request $request, Product $product)
    {
        $warehouseQty = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', 1)
            ->sum('quantity');

        $storesQty = StoreStock::where('product_id', $product->id)->sum('quantity');

        $warehouseValue = $warehouseQty * $product->cost_price;
        $storesValue = $storesQty * $product->cost_price;
        $totalValue = $warehouseValue + $storesValue;

        // Store-wise distribution
        $storeDistribution = StoreStock::where('store_stocks.product_id', $product->id) // FIXED: Qualified column
            ->join('store_details', 'store_stocks.store_id', '=', 'store_details.id')
            ->select([
                'store_details.store_name',
                'store_stocks.quantity',
                DB::raw('store_stocks.quantity * ' . $product->cost_price . ' as value')
            ])
            ->orderByDesc('value')
            ->get();

        // Batches at warehouse
        $batches = ProductBatch::where('product_id', $product->id)
            ->where('warehouse_id', 1)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        // Transaction history
        $transactions = StockTransaction::where('product_id', $product->id)
            ->with('user', 'store')
            ->latest()
            ->limit(20)
            ->get();

        return view('warehouse.stock-control.product-analytics', compact(
            'product',
            'warehouseQty',
            'storesQty',
            'warehouseValue',
            'storesValue',
            'totalValue',
            'storeDistribution',
            'batches',
            'transactions'
        ));
    }

    // ===== RECALL STOCK (RESTRUCTURED) =====
    public function recallStructured()
    {
        return view('warehouse.stock-control.recall.index');
    }

    // ===== RULES PAGE =====
    public function rules()
    {
        return view('warehouse.stock-control.rules');
    }
}