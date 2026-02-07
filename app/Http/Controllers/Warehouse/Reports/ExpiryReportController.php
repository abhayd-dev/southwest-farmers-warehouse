<?php

namespace App\Http\Controllers\Warehouse\Reports;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch; // Ensure you have this model
use App\Models\ProductCategory;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class ExpiryReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Fetch batches with stock > 0 in Warehouse
            $query = ProductBatch::with(['product', 'product.category', 'product.department'])
                ->where('warehouse_id', 1)
                ->where('quantity', '>', 0);

            // Filters
            if ($request->filled('department_id')) {
                $query->whereHas('product', fn($q) => $q->where('department_id', $request->department_id));
            }
            if ($request->filled('category_id')) {
                $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
            }
            if ($request->filled('status')) {
                $today = Carbon::today();
                if ($request->status === 'expired') {
                    $query->whereDate('expiry_date', '<', $today);
                } elseif ($request->status === 'critical') {
                    $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)]);
                } elseif ($request->status === 'warning') {
                    $query->whereBetween('expiry_date', [$today->copy()->addDays(31), $today->copy()->addDays(90)]);
                }
            }

            return DataTables::of($query)
                ->addColumn('product_name', function($row) {
                    return '<div>
                                <div class="fw-bold text-dark">'.$row->product->product_name.'</div>
                                <small class="text-muted">'.$row->product->sku.'</small>
                            </div>';
                })
                ->addColumn('department', fn($row) => $row->product->department->name ?? '-')
                ->addColumn('batch_info', fn($row) => '<span class="font-monospace">'.$row->batch_number.'</span>')
                ->addColumn('expiry_date', fn($row) => Carbon::parse($row->expiry_date)->format('d M Y'))
                ->addColumn('days_left', function($row) {
                    $days = Carbon::now()->diffInDays(Carbon::parse($row->expiry_date), false);
                    $days = (int)$days; // Cast to int

                    if ($days < 0) return '<span class="badge bg-dark">EXPIRED ('.abs($days).' days ago)</span>';
                    if ($days <= 30) return '<span class="badge bg-danger text-white">CRITICAL ('.$days.' days)</span>';
                    if ($days <= 90) return '<span class="badge bg-warning text-dark">WARNING ('.$days.' days)</span>';
                    return '<span class="badge bg-success bg-opacity-10 text-success">SAFE ('.$days.' days)</span>';
                })
                ->addColumn('stock_value', fn($row) => '$'.number_format($row->quantity * $row->product->cost_price, 2))
                ->rawColumns(['product_name', 'days_left', 'batch_info'])
                ->make(true);
        }

        $categories = ProductCategory::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();

        return view('warehouse.reports.expiry', compact('categories', 'departments'));
    }
}