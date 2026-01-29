<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockRequest;
use App\Models\RecallRequest;
use Yajra\DataTables\Facades\DataTables;

class DiscrepancyController extends Controller
{
    public function index()
    {
        return view('warehouse.discrepancy.index');
    }

    public function transferIssues(Request $request)
    {
        if ($request->ajax()) {
            // Logic: Find requests where Fulfilled (Dispatched) < Requested
            // OR where Fulfilled != Requested
            $query = StockRequest::with(['store', 'product'])
                ->where('status', 'completed')
                ->whereColumn('fulfilled_quantity', '<', 'requested_quantity');

            return DataTables::of($query)
                ->addColumn('request_id', fn($row) => '#REQ-' . $row->id)
                ->addColumn('store_name', fn($row) => $row->store->store_name ?? 'N/A')
                ->addColumn('product_name', fn($row) => $row->product->product_name ?? 'N/A')
                
                // Map Database Columns to Display Names
                ->addColumn('dispatched_quantity', fn($row) => $row->fulfilled_quantity) // "Sent"
                ->addColumn('received_quantity', fn($row) => $row->requested_quantity)   // "Requested" (Target)
                
                ->addColumn('discrepancy', function($row) {
                    $diff = $row->requested_quantity - $row->fulfilled_quantity;
                    return '<span class="badge bg-danger">Short: ' . $diff . '</span>';
                })
                ->addColumn('date', fn($row) => $row->updated_at->format('d M Y'))
                ->addColumn('action', function($row) {
                    return '<a href="'.route('warehouse.stock-requests.show', $row->id).'" class="btn btn-sm btn-outline-primary">View</a>';
                })
                ->rawColumns(['discrepancy', 'action'])
                ->make(true);
        }
    }

    public function storeReturns(Request $request)
    {
        if ($request->ajax()) {
            $query = RecallRequest::with(['store', 'product']);

            return DataTables::of($query)
                ->addColumn('recall_id', fn($row) => '#RET-' . $row->id)
                ->addColumn('store_name', fn($row) => $row->store->store_name ?? 'N/A')
                ->addColumn('product_name', fn($row) => $row->product->product_name ?? 'N/A')
                ->addColumn('requested_quantity', fn($row) => $row->quantity)
                ->addColumn('status_badge', function($row) {
                    $colors = [
                        'pending_store_approval' => 'warning',
                        'approved_by_store' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger'
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                })
                ->addColumn('reason', fn($row) => $row->reason ?? '-')
                ->addColumn('action', function($row) {
                    return '<a href="'.route('warehouse.stock-control.recall.show', $row->id).'" class="btn btn-sm btn-outline-primary">View</a>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
    }
}