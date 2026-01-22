<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StoreStock;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class StockControlController extends Controller
{
    public function overview()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $subcategories = ProductSubcategory::where('is_active', true)->get();
        return view('warehouse.stock-control.overview', compact('categories', 'subcategories'));
    }

    public function overviewData(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'subcategory'])
            ->select('products.*')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('coalesce(sum(quantity - reserved_quantity - damaged_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1),
                'total_stores_qty' => StoreStock::selectRaw('coalesce(sum(quantity - reserved_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ]);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->low_stock) {
            $query->havingRaw('(warehouse_qty + total_stores_qty) < 10');
        }

        return DataTables::of($query)
            ->addColumn('category_name', fn($row) => $row->category->name ?? '-')
            ->addColumn('subcategory_name', fn($row) => $row->subcategory->name ?? '-')
            ->addColumn('total_qty', fn($row) => $row->warehouse_qty + $row->total_stores_qty)
            ->addColumn('value', fn($row) => number_format(($row->warehouse_qty + $row->total_stores_qty) * $row->cost_price, 2))
            ->make(true);
    }

    public function expiryDamage()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        return view('warehouse.stock-control.expiry', compact('categories'));
    }

    public function expiryData(Request $request)
    {
        $days = $request->days ?? 60;
        $categoryId = $request->category_id;
        $damagedOnly = $request->damaged_only == 1;

        $query = ProductBatch::query()
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select([
                'product_batches.*',
                'products.product_name',
                'products.sku',
                'products.barcode',
                'product_categories.name as category_name',
                DB::raw('(product_batches.expiry_date - CURRENT_DATE) as days_left'), // ← FIXED: direct subtraction
                DB::raw('(product_batches.quantity * product_batches.cost_price) as value')
            ])
            ->where('product_batches.quantity', '>', 0);

        // Expiry filter
        if ($days !== 'all') {
            $query->whereRaw("product_batches.expiry_date <= CURRENT_DATE + INTERVAL '{$days} days'");
        } else {
            $query->where('product_batches.expiry_date', '<=', now()->addDays(90));
        }

        // Category filter
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        // Damaged only filter
        if ($damagedOnly) {
            $query->where('product_batches.damaged_quantity', '>', 0);
        }

        return DataTables::of($query)
            ->addColumn('status', function ($row) {
                $daysLeft = $row->days_left;
                if ($daysLeft <= 0) {
                    return '<span class="badge bg-danger">Expired</span>';
                }
                if ($daysLeft <= 15) {
                    return '<span class="badge bg-danger">Critical (' . $daysLeft . ' days)</span>';
                }
                if ($daysLeft <= 30) {
                    return '<span class="badge bg-warning">Urgent (' . $daysLeft . ' days)</span>';
                }
                return '<span class="badge bg-info">Warning (' . $daysLeft . ' days)</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="btn-group btn-group-sm">
                    <a href="' . route('warehouse.stocks.history', $row->product_id) . '" 
                       class="btn btn-outline-info">History</a>
                    <button class="btn btn-outline-warning recall-btn" 
                            data-product="' . $row->product_id . '" 
                            data-batch="' . $row->id . '">Recall</button>
                </div>
            ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function valuation()
    {
        // Current Totals (safe & correct)
        $warehouseValue = ProductStock::where('warehouse_id', 1)
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('product_stocks.quantity * products.cost_price'));

        $storesValue = StoreStock::join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(DB::raw('store_stocks.quantity * products.cost_price'));

        $totalValue = $warehouseValue + $storesValue;

        // 30-Day Trend – Fixed: Use current cost_price × quantity change (no cost_price in transactions)
        $trend = DB::table('stock_transactions')
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity_change) as net_qty_change')
            ->where('created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Approximate daily value change using average cost_price (fallback if no products)
        $averageCost = Product::where('cost_price', '>', 0)->avg('cost_price') ?: 1;

        $dates = [];
        $valueTrend = [];
        $cumulative = $totalValue;

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $dayData = $trend->firstWhere('date', $date);
            $netChange = $dayData ? $dayData->net_qty_change : 0;
            $valueChange = $netChange * $averageCost;

            $cumulative -= $valueChange;
            $valueTrend[] = max(0, round($cumulative, 2));
        }

        $dates = array_reverse($dates);
        $valueTrend = array_reverse($valueTrend);

        // Top Products – Fixed: Proper alias + groupBy columns
        $products = Product::query()
            ->select([
                'products.id',
                'products.product_name',
                'products.sku',
                DB::raw('COALESCE(SUM(product_stocks.quantity), 0) as warehouse_qty'),
                DB::raw('COALESCE(SUM(product_stocks.quantity * products.cost_price), 0) as warehouse_value'),
                DB::raw('COALESCE(SUM(store_stocks.quantity), 0) as stores_qty'),
                DB::raw('COALESCE(SUM(store_stocks.quantity * products.cost_price), 0) as stores_value'),
                DB::raw('(COALESCE(SUM(product_stocks.quantity * products.cost_price), 0) + 
                      COALESCE(SUM(store_stocks.quantity * products.cost_price), 0)) as total_value')
            ])
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->leftJoin('store_stocks', 'products.id', '=', 'store_stocks.product_id')
            ->groupBy('products.id', 'products.product_name', 'products.sku')
            ->orderByDesc('total_value')
            ->limit(15)
            ->get();

        return view('warehouse.stock-control.valuation', compact(
            'warehouseValue',
            'storesValue',
            'totalValue',
            'dates',
            'valueTrend',
            'products'
        ));
    }
}
