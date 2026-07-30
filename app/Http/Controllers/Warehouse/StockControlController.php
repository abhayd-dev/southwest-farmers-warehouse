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
use App\Models\Department; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class StockControlController extends Controller
{
    // ===== STOCK OVERVIEW =====
    public function overview()
    {

        set_time_limit(300);
        $categories = ProductCategory::where('is_active', true)->get();
        $subcategories = ProductSubcategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('warehouse.stock-control.overview', compact('categories', 'subcategories', 'departments'));
    }

    public function overviewData(Request $request)
    {
        set_time_limit(300);
        $viewType = $request->get('view_type', 'warehouse');

        $query = Product::query()
            ->whereNull('products.store_id')
            ->with(['category', 'subcategory', 'department'])
            ->select('products.*')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity - damaged_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1),
                'total_stores_qty' => StoreStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ]);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        return DataTables::of($query)
            ->addColumn('department_name', fn($row) => $row->department->name ?? '-')
            ->addColumn('category_name', fn($row) => $row->category->name ?? '-')
            ->addColumn('subcategory_name', fn($row) => $row->subcategory->name ?? '-')
            ->addColumn('display_qty', fn($row) => $viewType === 'store' ? (int)$row->total_stores_qty : (int)$row->warehouse_qty)
            ->addColumn('total_qty', fn($row) => (int)$row->warehouse_qty + (int)$row->total_stores_qty)
            ->addColumn('value', function($row) use ($viewType) {
                $qty = $viewType === 'store' ? (int)$row->total_stores_qty : (int)$row->warehouse_qty;
                return number_format($qty * ($row->cost_price ?? 0), 2);
            })
            ->filterColumn('product_name', function($query, $keyword) {
                $query->where('products.product_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('upc', function($query, $keyword) {
                $query->where('products.upc', 'like', "%{$keyword}%");
            })
            ->rawColumns(['department_name', 'category_name', 'subcategory_name'])
            ->make(true);
    }

    public function exportOverview(Request $request)
    {
        set_time_limit(300);
        $viewType = $request->get('view_type', 'warehouse');
        $fileName = ($viewType === 'store' ? 'Store_Stock_Overview_' : 'Warehouse_Stock_Overview_') . date('Y_m_d_His') . '.csv';

        $query = Product::query()
            ->whereNull('products.store_id')
            ->with(['category', 'subcategory', 'department'])
            ->select('products.*')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity - damaged_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1),
                'total_stores_qty' => StoreStock::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ]);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        $products = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $columns = ['Product', 'UPC', 'Department', 'Category', 'Subcategory', $viewType === 'store' ? 'Store Qty' : 'Warehouse Qty', 'Cost Value'];

        $callback = function () use ($products, $columns, $viewType) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $row) {
                $qty = $viewType === 'store' ? (int)$row->total_stores_qty : (int)$row->warehouse_qty;
                $val = number_format($qty * ($row->cost_price ?? 0), 2, '.', '');

                fputcsv($file, [
                    $row->product_name,
                    $row->upc ?? '-',
                    $row->department->name ?? '-',
                    $row->category->name ?? '-',
                    $row->subcategory->name ?? '-',
                    $qty,
                    '$' . $val
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ===== STOCK VALUATION =====
    public function valuation(Request $request)
    {
        set_time_limit(300);
        $stores = StoreDetail::where('is_active', true)->get();
        $categories = ProductCategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        $warehouseValue = ProductStock::where('warehouse_id', 1)
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('product_stocks.quantity * products.cost_price'));

        $storesValue = StoreStock::join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('store_stocks.quantity * products.cost_price'));

        $totalWarehouseUnits = ProductStock::where('warehouse_id', 1)->sum('quantity');
        $totalStoreUnits = StoreStock::sum('quantity');

        return view('warehouse.stock-control.valuation', compact(
            'warehouseValue',
            'storesValue',
            'totalWarehouseUnits',
            'totalStoreUnits',
            'stores',
            'categories',
            'departments'
        ));
    }

    public function valuationData(Request $request)
    {
        $viewType = $request->get('view_type', 'warehouse');

        $query = Product::query()
            ->whereNull('products.store_id')
            ->with(['department', 'category'])
            ->select([
                'products.id',
                'products.product_name',
                'products.upc',
                'products.cost_price',
                'products.department_id',
                'products.category_id',
                DB::raw('COALESCE(SUM(product_stocks.quantity), 0) as warehouse_qty'),
                DB::raw('COALESCE(SUM(product_stocks.quantity * products.cost_price), 0) as warehouse_value'),
                DB::raw('COALESCE(SUM(store_stocks.quantity), 0) as stores_qty'),
                DB::raw('COALESCE(SUM(store_stocks.quantity * products.cost_price), 0) as stores_value'),
            ])
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->leftJoin('store_stocks', 'products.id', '=', 'store_stocks.product_id')
            ->groupBy('products.id', 'products.product_name', 'products.upc', 'products.cost_price', 'products.department_id', 'products.category_id');

        if ($request->filled('department_id')) {
            $query->where('products.department_id', $request->department_id);
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($viewType === 'store' && $request->filled('store_id')) {
            $query->whereHas('storeStocks', fn($q) => $q->where('store_stocks.store_id', $request->store_id));
        }

        if ($request->filled('search_term')) {
            $term = strtolower($request->search_term);
            $query->where(function($q) use ($term) {
                $q->where(DB::raw('LOWER(products.product_name)'), 'like', "%{$term}%")
                  ->orWhere(DB::raw('LOWER(products.upc)'), 'like', "%{$term}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('department_name', fn($row) => $row->department->name ?? '-')
            ->addColumn('whse_cost_value_fmt', fn($row) => '$ ' . number_format($row->cost_price ?? 0, 2))
            ->addColumn('warehouse_value_fmt', fn($row) => '$ ' . number_format($row->warehouse_value, 2))
            ->addColumn('stores_value_fmt', fn($row) => '$ ' . number_format($row->stores_value, 2))
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stock-control.valuation.product', $row->id) . '" 
                   class="btn btn-sm btn-outline-primary">
                   <i class="mdi mdi-chart-line me-1"></i> Analytics
                </a>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    // ... (Keep storeValuation, storeAnalytics, productAnalytics, recallStructured, rules as they were) ...
    public function storeValuation(Request $request)
    {
        set_time_limit(300);
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

    public function storeAnalytics(Request $request, StoreDetail $store)
    {
        set_time_limit(300);
        $storeValue = StoreStock::where('store_stocks.store_id', $store->id)
            ->join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('store_stocks.quantity * products.cost_price'));

        $storeQty = StoreStock::where('store_stocks.store_id', $store->id)->sum('quantity');

        $topProducts = StoreStock::where('store_stocks.store_id', $store->id)
            ->join('products', 'store_stocks.product_id', '=', 'products.id')
            ->select([
                'products.product_name',
                'products.upc',
                'store_stocks.quantity',
                DB::raw('store_stocks.quantity * products.cost_price as value')
            ])
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $trend = StockTransaction::where('stock_transactions.store_id', $store->id)
            ->where('created_at', '>=', Carbon::today()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('warehouse.stock-control.store-analytics', compact('store', 'storeValue', 'storeQty', 'topProducts', 'trend'));
    }

    public function productAnalytics(Request $request, Product $product)
    {
        set_time_limit(300);

        // FIX: Ensure cost price is never null (default to 0)
        $costPrice = $product->cost_price ?? 0;

        $warehouseQty = ProductStock::where('product_id', $product->id)->where('warehouse_id', 1)->sum('quantity');
        $storesQty = StoreStock::where('product_id', $product->id)->sum('quantity');

        $warehouseValue = $warehouseQty * $costPrice;
        $storesValue = $storesQty * $costPrice;
        $totalValue = $warehouseValue + $storesValue;

        $storeDistribution = StoreStock::where('store_stocks.product_id', $product->id)
            ->join('store_details', 'store_stocks.store_id', '=', 'store_details.id')
            ->select([
                'store_details.store_name',
                'store_stocks.quantity',
                'store_stocks.updated_at as last_activity',
                // FIX: Use the variable $costPrice (which is 0 if null)
                DB::raw('store_stocks.quantity * ' . $costPrice . ' as value')
            ])
            ->orderByDesc('value')
            ->get();

        $thirtyDaysActivity = StockTransaction::where('product_id', $product->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->sum(DB::raw('ABS(quantity_change)'));

        $batches = ProductBatch::where('product_id', $product->id)
            ->where('warehouse_id', 1)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $txnQuery = StockTransaction::where('product_id', $product->id)
            ->with('user', 'store');

        if ($request->filled('from_date')) {
            $txnQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $txnQuery->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $txnQuery->latest()
            ->limit(50)
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
            'transactions',
            'thirtyDaysActivity'
        ));
    }

    public function recallStructured()
    {
        return view('warehouse.stock-control.recall.index');
    }
    public function rules()
    {
        return view('warehouse.stock-control.rules');
    }
}
