<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\ProductCategory; // Ensure this model exists or use DB table
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class FinanceReportController extends Controller
{
    // ===== 1. FINANCE OVERVIEW (Dashboard) =====
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // --- KPIS ---
        $totalProcurement = PurchaseOrder::where('status', 'completed')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');

        $totalDispatchValue = DB::table('stock_requests')
            ->join('products', 'stock_requests.product_id', '=', 'products.id')
            ->where('stock_requests.status', 'completed')
            ->whereBetween('stock_requests.updated_at', [$startDate, $endDate])
            ->sum(DB::raw('stock_requests.fulfilled_quantity * products.cost_price'));

        $currentInventoryValue = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('product_stocks.warehouse_id', 1)
            ->sum(DB::raw('product_stocks.quantity * products.cost_price'));

        // --- CHART 1: SPEND VS DISPATCH TREND (Line Chart) ---
        // Get daily sums for the selected range
        $procurementTrend = PurchaseOrder::selectRaw('DATE(order_date) as date, SUM(total_amount) as total')
            ->where('status', 'completed')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')->toArray();

        $dispatchTrend = DB::table('stock_requests')
            ->join('products', 'stock_requests.product_id', '=', 'products.id')
            ->selectRaw('DATE(stock_requests.updated_at) as date, SUM(stock_requests.fulfilled_quantity * products.cost_price) as total')
            ->where('stock_requests.status', 'completed')
            ->whereBetween('stock_requests.updated_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')->toArray();

        // Fill missing dates with 0 for the chart
        $chartLabels = [];
        $procurementData = [];
        $dispatchData = [];
        
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $fDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $procurementData[] = $procurementTrend[$fDate] ?? 0;
            $dispatchData[] = $dispatchTrend[$fDate] ?? 0;
        }

        // --- CHART 2: INVENTORY VALUE BY CATEGORY (Doughnut Chart) ---
        $categoryDistribution = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('product_stocks.warehouse_id', 1)
            ->groupBy('product_categories.name')
            ->selectRaw('product_categories.name, SUM(product_stocks.quantity * products.cost_price) as total_value')
            ->orderByDesc('total_value')
            ->limit(6) // Top 6 Categories
            ->get();

        $pieLabels = $categoryDistribution->pluck('name');
        $pieData = $categoryDistribution->pluck('total_value');

        return view('warehouse.finance.index', compact(
            'totalProcurement', 
            'totalDispatchValue', 
            'currentInventoryValue',
            'startDate', 'endDate',
            'chartLabels', 'procurementData', 'dispatchData', // Line Chart
            'pieLabels', 'pieData' // Pie Chart
        ));
    }

    // ===== 2. TRANSACTION LEDGER =====
    public function ledger(Request $request)
    {
        if ($request->ajax()) {
            $query = StockTransaction::with(['product', 'store', 'user']);

            // Filters
            if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);
            if ($request->filled('type')) $query->where('type', $request->type);
            if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

            return DataTables::of($query)
                ->addColumn('date', fn($row) => $row->created_at->format('d M Y, h:i A'))
                ->addColumn('type_badge', function ($row) {
                    $badges = [
                        'purchase_in' => ['color' => 'success', 'icon' => 'mdi-arrow-down-bold'],
                        'dispatch' => ['color' => 'info', 'icon' => 'mdi-truck-delivery'],
                        'recall_in' => ['color' => 'warning', 'icon' => 'mdi-undo'],
                        'adjustment' => ['color' => 'secondary', 'icon' => 'mdi-tune'],
                        'return' => ['color' => 'danger', 'icon' => 'mdi-alert-circle'],
                    ];
                    $config = $badges[$row->type] ?? ['color' => 'primary', 'icon' => 'mdi-circle'];
                    
                    return '<span class="badge bg-'.$config['color'].' bg-opacity-10 text-'.$config['color'].' px-2 py-1">
                                <i class="mdi '.$config['icon'].' me-1"></i> '.strtoupper(str_replace('_', ' ', $row->type)).'
                            </span>';
                })
                ->addColumn('product_details', function($row) {
                    return '<div>
                                <span class="fw-bold text-dark">'.$row->product->product_name.'</span><br>
                                <small class="text-muted">SKU: '.$row->product->sku.'</small>
                            </div>';
                })
                ->addColumn('quantity', function ($row) {
                    $color = $row->quantity_change > 0 ? 'text-success' : 'text-danger';
                    $sign = $row->quantity_change > 0 ? '+' : '';
                    return '<span class="fw-bold fs-6 ' . $color . '">' . $sign . $row->quantity_change . '</span>';
                })
                ->addColumn('balance', fn($row) => '<span class="fw-bold text-dark">'.number_format($row->running_balance).'</span>')
                ->addColumn('reference', fn($row) => '<span class="font-monospace text-muted">'.$row->reference_id.'</span>')
                ->addColumn('user', fn($row) => $row->user->name ?? 'System')
                ->rawColumns(['type_badge', 'product_details', 'quantity', 'balance', 'reference'])
                ->make(true);
        }

        $products = Product::select('id', 'product_name')->orderBy('product_name')->get();
        return view('warehouse.finance.ledger', compact('products'));
    }

    public function exportLedger(Request $request)
    {
        // ... (Export logic remains the same as previous response) ...
        // Re-paste if you need the export function again
        $fileName = 'ledger_' . date('Y-m-d') . '.csv';
        $query = StockTransaction::with(['product', 'store', 'user']);
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);
        $transactions = $query->latest()->get();

        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        $callback = function() use($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Product', 'SKU', 'Qty Change', 'Balance', 'Ref ID', 'User']);
            foreach ($transactions as $row) {
                fputcsv($file, [$row->created_at, $row->type, $row->product->product_name, $row->product->sku, $row->quantity_change, $row->running_balance, $row->reference_id, $row->user->name ?? 'System']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}