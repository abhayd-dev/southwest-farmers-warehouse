<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\StockTransaction;
use App\Models\Sale;
use App\Models\StoreDetail;
use App\Models\ProductCategory;
use App\Models\WareUser;
use App\Models\ProductMinMaxLevel;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\SimpleExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('warehouse.reports.index');
    }

    public function dashboard()
    {
        $totalProducts = Product::whereNull('store_id')->count();
        $totalStockValue = Product::whereNull('store_id')->get()->sum(function($p) {
            return ($p->stock->quantity ?? 0) * ($p->cost_price ?? 0);
        });
        
        $lowStockCount = Product::whereNull('store_id')->whereHas('stock', function($q) {
            $q->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('product_min_max_levels')
                    ->whereColumn('product_min_max_levels.product_id', 'product_stocks.product_id')
                    ->whereColumn('product_stocks.quantity', '<=', 'product_min_max_levels.min_level');
            });
        })->count();

        $recentReceivings = StockTransaction::whereIn('type', ['purchase_in', 'receive'])
            ->with(['product', 'purchaseOrder.vendor'])
            ->latest()
            ->limit(10)
            ->get();

        return view('warehouse.reports.dashboard', compact(
            'totalProducts', 
            'totalStockValue', 
            'lowStockCount', 
            'recentReceivings'
        ));
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $storeId = $request->input('store_id');

        $query = Sale::with('store')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($storeId) $query->where('store_id', $storeId);

        $sales = $query->latest()->paginate(20);
        $totalSales = $query->sum('total_amount');
        $totalTransactions = $query->count();
        $stores = StoreDetail::all();

        return view('warehouse.reports.sales', compact('sales', 'totalSales', 'totalTransactions', 'stores', 'startDate', 'endDate', 'storeId'));
    }

    public function stockMovement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $type = $request->input('type');

        $query = StockTransaction::with(['product', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($type) $query->where('type', $type);
            
        $transactions = $query->latest()->paginate(50);
        $types = ['purchase_in' => 'Receiving', 'dispatch' => 'Dispatch', 'adjustment' => 'Adjustment', 'return' => 'Return', 'receive' => 'Receive (Legacy)'];

        return view('warehouse.reports.stock_movement', compact('transactions', 'startDate', 'endDate', 'types', 'type'));
    }

    public function inventoryHealth()
    {
        $lowStockItems = Product::whereNull('store_id')
            ->whereHas('stock', function($q) {
                $q->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('product_min_max_levels')
                        ->whereColumn('product_min_max_levels.product_id', 'product_stocks.product_id')
                        ->whereColumn('product_stocks.quantity', '<=', 'product_min_max_levels.min_level');
                });
            })->with(['stock.warehouse', 'category'])
            ->addSelect([
                'warehouse_qty' => \App\Models\ProductStock::select('quantity')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1)
            ])
            ->paginate(10);

        $expiringBatches = ProductBatch::with('product')
            ->where('warehouse_id', 1)
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->where('quantity', '>', 0)
            ->get();

        $expiredBatches = ProductBatch::with('product')
            ->where('warehouse_id', 1)
            ->where('expiry_date', '<', Carbon::now())
            ->where('quantity', '>', 0)
            ->get();

        return view('warehouse.reports.inventory_health', compact('lowStockItems', 'expiringBatches', 'expiredBatches'));
    }

    public function fastMoving(Request $request)
    {
        $days = $request->input('days', 30);
        $results = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sale_items.created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                'products.id', 
                'products.product_name', 
                'products.upc', 
                'products.unit',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.quantity * sale_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.product_name', 'products.upc', 'products.unit')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $items = $results->map(function($item) {
            $item->product = (object)[
                'product_name' => $item->product_name,
                'upc' => $item->upc,
                'unit' => $item->unit
            ];
            return $item;
        });

        return view('warehouse.reports.fast_moving', compact('items', 'days'));
    }

    public function topDispatched(Request $request)
    {
        $days = $request->input('days', 30);
        
        $baseQuery = StockTransaction::where('type', 'dispatch')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->with('product')
            ->select('product_id', DB::raw('SUM(ABS(quantity_change)) as total_dispatched'))
            ->groupBy('product_id')
            ->orderByDesc('total_dispatched');

        $weightBased = (clone $baseQuery)->whereHas('product', function($q) {
            $q->whereIn('unit', ['kg', 'lb', 'g', 'oz']);
        })->limit(20)->get();

        $unitBased = (clone $baseQuery)->whereHas('product', function($q) {
            $q->whereNotIn('unit', ['kg', 'lb', 'g', 'oz', 'Kgs', 'KG', 'LBS']);
        })->limit(20)->get();

        return view('warehouse.reports.top_dispatched', compact('weightBased', 'unitBased', 'days'));
    }

    public function warehouseMin()
    {
        $lowStockItems = Product::whereNull('store_id')
            ->join('product_min_max_levels', 'products.id', '=', 'product_min_max_levels.product_id')
            ->whereHas('stock', function($q) {
                $q->whereColumn('product_stocks.quantity', '<=', 'product_min_max_levels.min_level');
            })
            ->select('products.*', 'product_min_max_levels.min_level')
            ->with(['stock', 'category'])
            ->paginate(50);

        return view('warehouse.reports.warehouse_min', compact('lowStockItems'));
    }

    public function salesByPricePoint(Request $request)
    {
        $days = $request->input('days', 30);
        
        $items = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                'sale_items.product_id',
                'sale_items.price as unit_price',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.quantity * sale_items.price) as revenue')
            )
            ->groupBy('sale_items.product_id', 'sale_items.price')
            ->get();

        $productIds = $items->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $groupedProducts = $items->map(function($item) use ($products) {
            $item->product = $products->get($item->product_id);
            return $item;
        })->filter(fn($i) => !is_null($i->product))->groupBy('product_id');

        return view('warehouse.reports.sales_by_price_point', compact('groupedProducts', 'days'));
    }

    public function masterInventory(Request $request)
    {
        $categoryId = $request->input('category_id');
        $query = Product::whereNull('store_id')->with(['category', 'stock']);
        if ($categoryId) $query->where('category_id', $categoryId);
        $products = $query->paginate(50);
        $categories = ProductCategory::all();
        return view('warehouse.reports.master_inventory', compact('products', 'categories', 'categoryId'));
    }

    public function openPurchaseOrders(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $userId = $request->input('user_id');
        $query = PurchaseOrder::with(['vendor', 'creator'])->whereIn('status', ['ordered', 'partial']);
        if ($vendorId) $query->where('vendor_id', $vendorId);
        if ($userId) $query->where('created_by', $userId);
        
        $orders = $query->paginate(50);
        $vendors = Vendor::all();
        $users = WareUser::all();
        
        return view('warehouse.reports.open_purchase_orders', compact('orders', 'vendors', 'users', 'vendorId', 'userId'));
    }

    public function receivingVariance(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $categoryId = $request->input('category_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = PurchaseOrderItem::with(['purchaseOrder.vendor', 'product.category'])
            ->whereHas('purchaseOrder', function($q) use ($vendorId, $startDate, $endDate) {
                $q->whereIn('status', ['partial', 'completed']);
                if ($vendorId) $q->where('vendor_id', $vendorId);
                if ($startDate) $q->whereDate('updated_at', '>=', $startDate);
                if ($endDate) $q->whereDate('updated_at', '<=', $endDate);
            })
            ->whereRaw('received_quantity != requested_quantity');

        if ($categoryId) {
            $query->whereHas('product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $variances = $query->paginate(50);
        $vendors = Vendor::all();
        $categories = ProductCategory::all();

        return view('warehouse.reports.receiving_variance', compact('variances', 'vendors', 'categories', 'vendorId', 'categoryId', 'startDate', 'endDate'));
    }

    public function receivingHistory(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $vendorId = $request->input('vendor_id');
        $userId = $request->input('user_id');

        $query = StockTransaction::with(['product', 'purchaseOrder.vendor', 'user', 'batch'])
            ->whereIn('type', ['purchase_in', 'receive']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        if ($vendorId) {
            $query->whereHas('purchaseOrder', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        }
        if ($userId) $query->where('ware_user_id', $userId);

        $history = $query->latest()->paginate(50);
        $vendors = Vendor::all();
        $users = WareUser::all();

        return view('warehouse.reports.receiving_history', compact('history', 'vendors', 'users', 'vendorId', 'userId', 'startDate', 'endDate'));
    }

    public function vendorPerformance(Request $request)
    {
        $vendors = Vendor::withCount(['purchaseOrders as po_count' => function($q) { $q->where('status', 'completed'); }])->get();
        return view('warehouse.reports.vendor_performance', compact('vendors'));
    }

    public function purchasePriceVariance(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = StockTransaction::with('product')->whereIn('type', ['purchase_in', 'receive']);
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        $variances = $query->latest()->paginate(50);
        return view('warehouse.reports.purchase_price_variance', compact('variances', 'startDate', 'endDate'));
    }

    public function grni(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $query = PurchaseOrder::with('vendor')->whereIn('status', ['partial', 'completed'])->where('payment_status', '!=', 'paid');
        if ($vendorId) $query->where('vendor_id', $vendorId);
        $orders = $query->paginate(50);
        $vendors = Vendor::all();
        return view('warehouse.reports.grni', compact('orders', 'vendors', 'vendorId'));
    }

    public function reorderSuggestion(Request $request)
    {
        $categoryId = $request->input('category_id');
        $query = Product::whereNull('store_id')
            ->whereHas('stock', function($q) {
                $q->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('product_min_max_levels')
                        ->whereColumn('product_min_max_levels.product_id', 'product_stocks.product_id')
                        ->whereColumn('product_stocks.quantity', '<=', 'product_min_max_levels.min_level');
                });
            })->with(['category', 'stock', 'minMaxLevel']);
            
        if ($categoryId) $query->where('category_id', $categoryId);
        
        $suggestions = $query->paginate(50);
        $categories = ProductCategory::all();
        return view('warehouse.reports.reorder_suggestion', compact('suggestions', 'categories', 'categoryId'));
    }

    public function export(Request $request)
    {
        $report = $request->input('report');
        $format = $request->input('format', 'excel'); 
        $data = [];
        $headings = [];
        $title = str_replace('-', ' ', strtoupper($report));

        if ($report === 'master-inventory') {
            $query = Product::whereNull('store_id')->with(['category', 'stock']);
            if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
            $products = $query->get();
            $headings = ['SKU/UPC', 'Item Description', 'Category', 'Location', 'Qty on Hand', 'Landed Cost', 'Total Valuation', 'Retail Price', 'Margin %'];
            foreach ($products as $product) {
                $landedCost = $product->cost_price ?? 0;
                $qtyOnHand = $product->stock->quantity ?? 0;
                $totalValuation = $qtyOnHand * $landedCost;
                $margin = $product->retail_price > 0 ? (($product->retail_price - $landedCost) / $product->retail_price) * 100 : 0;
                $data[] = [$product->upc ?? $product->sku, $product->product_name, $product->category->name ?? 'N/A', 'Warehouse', $qtyOnHand, '$' . number_format($landedCost, 2), '$' . number_format($totalValuation, 2), '$' . number_format($product->retail_price ?? 0, 2), number_format($margin, 2) . '%'];
            }
        } elseif ($report === 'receiving-variance') {
            $headings = ['PO Number', 'Product', 'Requested Qty', 'Received Qty', 'Variance', 'Unit Cost', 'Cost Diff', 'Type'];
            $query = PurchaseOrderItem::with(['purchaseOrder.vendor', 'product.category'])->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->whereIn('status', ['partial', 'completed']);
                if ($request->filled('start_date')) $q->whereDate('updated_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('updated_at', '<=', $request->end_date);
                if ($request->filled('vendor_id')) $q->where('vendor_id', $request->vendor_id);
            })->whereRaw('received_quantity != requested_quantity');
            if ($request->filled('category_id')) { $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id)); }
            $items = $query->get();
            foreach ($items as $item) {
                $variance = $item->requested_quantity - $item->received_quantity;
                $data[] = [$item->purchaseOrder->po_number, $item->product->product_name, $item->requested_quantity, $item->received_quantity, $variance, '$' . number_format($item->unit_cost, 2), '$' . number_format($variance * $item->unit_cost, 2), $variance > 0 ? 'Shortage' : 'Overage'];
            }
        } elseif ($report === 'open-purchase-orders') {
            $headings = ['PO Number', 'Vendor', 'Date', 'Estimated Amount', 'Status', 'Created By'];
            $query = PurchaseOrder::with(['vendor', 'creator'])->whereIn('status', ['ordered', 'partial']);
            if ($request->filled('vendor_id')) $query->where('vendor_id', $request->vendor_id);
            if ($request->filled('user_id')) $query->where('created_by', $request->user_id);
            $orders = $query->get();
            foreach ($orders as $order) { $data[] = [$order->po_number, $order->vendor->name, $order->order_date, '$' . number_format($order->total_amount, 2), strtoupper($order->status), $order->creator->name ?? 'N/A']; }
        } elseif ($report === 'reorder-suggestion') {
            $headings = ['Product', 'UPC', 'Category', 'Current Stock', 'Min Level', 'Max Level', 'Suggestion'];
            $query = Product::with(['stock', 'category', 'minMaxLevel'])->whereNull('store_id');
            if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
            $products = $query->get();
            foreach ($products as $p) {
                $qty = $p->stock->quantity ?? 0;
                $minLevel = $p->minMaxLevel->min_level ?? 0;
                $maxLevel = $p->minMaxLevel->max_level ?? 0;
                $data[] = [$p->product_name, $p->upc, $p->category->name ?? 'N/A', $qty, $minLevel, $maxLevel, ($qty < $minLevel) ? 'REORDER NOW' : 'OK'];
            }
        } elseif ($report === 'vendor-performance') {
            $headings = ['Vendor', 'Total Completed POs', 'Total Spend'];
            $vendors = Vendor::withCount(['purchaseOrders as po_count' => function ($q) { $q->where('status', 'completed'); }])->get();
            foreach ($vendors as $v) { $spend = PurchaseOrder::where('vendor_id', $v->id)->where('status', 'completed')->sum('total_amount'); $data[] = [$v->name, $v->po_count, '$' . number_format($spend, 2)]; }
        } elseif ($report === 'receiving-history') {
            $headings = ['Date', 'Staff', 'Vendor', 'PO Number', 'Product', 'Qty Received', 'Unit Cost', 'Batch No'];
            $query = StockTransaction::with(['product', 'user', 'batch', 'purchaseOrder.vendor'])->whereIn('type', ['purchase_in', 'receive']);
            if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);
            $transactions = $query->latest()->get();
            foreach ($transactions as $tx) { $data[] = [$tx->created_at->format('d M Y H:i'), $tx->user->name ?? 'N/A', $tx->vendor->name ?? 'N/A', $tx->reference_no ?? 'N/A', $tx->product->product_name, abs($tx->quantity_change), '$' . number_format($tx->product->cost_price ?? 0, 2), $tx->batch->batch_number ?? 'N/A']; }
        } elseif ($report === 'purchase-price-variance') {
            $headings = ['Date', 'Product', 'Received Price', 'Std Cost Price', 'Variance'];
            $query = StockTransaction::with(['product'])->whereIn('type', ['purchase_in', 'receive']);
            if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);
            $transactions = $query->latest()->get();
            foreach ($transactions as $tx) {
                $receivedPrice = $tx->unit_price ?? $tx->product->cost_price;
                $variance = $receivedPrice - $tx->product->cost_price;
                $data[] = [$tx->created_at->format('d M Y H:i'), $tx->product->product_name, '$' . number_format($receivedPrice, 2), '$' . number_format($tx->product->cost_price, 2), '$' . number_format($variance, 2)];
            }
        } elseif ($report === 'grni') {
            $headings = ['PO Number', 'Vendor', 'Date', 'Total Amount', 'Payment Status'];
            $query = PurchaseOrder::with(['vendor'])->whereIn('status', ['partial', 'completed'])->where('payment_status', '!=', 'paid');
            if ($request->filled('vendor_id')) $query->where('vendor_id', $request->vendor_id);
            $orders = $query->get();
            foreach ($orders as $order) { $data[] = [$order->po_number, $order->vendor->name, $order->order_date, '$' . number_format($order->total_amount, 2), strtoupper($order->payment_status)]; }
        }

        if (empty($data)) { return back()->with('info', 'No data available to export.'); }
        $extension = $format === 'csv' ? 'csv' : ($format === 'pdf' ? 'pdf' : 'xlsx');
        $fileName = str_replace('-', '_', $report) . '_' . date('Y_m-d') . '.' . $extension;
        if ($format === 'pdf') { $pdf = Pdf::loadView('warehouse.reports.export_pdf', compact('data', 'headings', 'title')); return $pdf->download($fileName); }
        return Excel::download(new SimpleExport($data, $headings), $fileName);
    }
}
