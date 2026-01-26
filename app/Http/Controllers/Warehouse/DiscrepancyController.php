<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StockRequest;
use App\Models\RecallRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class DiscrepancyController extends Controller
{
    public function index()
    {
        return view('warehouse.discrepancy.index');
    }

    public function transferIssues(Request $request)
    {
        $query = StockRequest::with(['store', 'product'])
            ->where('status', 'completed')
            ->whereColumn('dispatched_quantity', '>', 'received_quantity');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->addColumn('request_id', fn($row) => '#REQ-' . str_pad($row->id, 5, '0', STR_PAD_LEFT))
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('discrepancy', function($row) {
                $diff = $row->dispatched_quantity - $row->received_quantity;
                return '<span class="text-danger fw-bold">-' . $diff . '</span>';
            })
            ->addColumn('date', fn($row) => $row->updated_at ? Carbon::parse($row->updated_at)->format('d M Y') : '-')
            ->addColumn('action', fn($row) => '
                <a href="'.route('warehouse.stock-requests.show', $row->id).'" class="btn btn-sm btn-outline-primary">
                    View
                </a>
            ')
            ->rawColumns(['discrepancy', 'action'])
            ->make(true);
    }

    public function storeReturns(Request $request)
    {
        $query = RecallRequest::with(['store', 'product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('recall_id', fn($row) => '#RET-' . str_pad($row->id, 5, '0', STR_PAD_LEFT))
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . $row->getStatusColor() . '">' . $row->getStatusLabel() . '</span>')
            ->addColumn('action', fn($row) => '
                <a href="'.route('warehouse.stock-control.recall.show', $row->id).'" class="btn btn-sm btn-outline-primary">
                    Manage
                </a>
            ')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }
}