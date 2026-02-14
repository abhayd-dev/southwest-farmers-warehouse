<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class TransferMonitorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Fetch all transfers between stores
            $query = StockTransfer::with(['fromStore', 'toStore', 'product'])
                ->select('stock_transfers.*'); // Avoid column collision

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('transfer_no', function ($row) {
                    return '<span class="fw-bold text-primary">#' . $row->transfer_number . '</span>';
                })
                ->addColumn('route', function ($row) {
                    return '<div class="d-flex align-items-center">
                                <span class="fw-semibold">' . $row->fromStore->store_name . '</span>
                                <i class="mdi mdi-arrow-right mx-2 text-muted"></i>
                                <span class="fw-semibold">' . $row->toStore->store_name . '</span>
                            </div>';
                })
                ->addColumn('items_count', function ($row) {
                    return $row->quantity . ' Units'; // Assuming 'quantity' is a column in stock_transfers
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->created_at)->format('d M Y');
                })
                ->addColumn('status', function ($row) {
                    $badges = [
                        'pending' => 'warning', // Waiting for Store B to accept
                        'in_transit' => 'info', // On the truck
                        'completed' => 'success', // Received
                        'rejected' => 'danger'
                    ];
                    $status = ucfirst(str_replace('_', ' ', $row->status));
                    return '<span class="badge bg-' . ($badges[$row->status] ?? 'secondary') . '">' . $status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    // Read Only View Button
                    return '<button class="btn btn-sm btn-outline-dark view-details" data-id="' . $row->id . '">
                                <i class="mdi mdi-eye"></i> View
                            </button>';
                })
                ->rawColumns(['transfer_no', 'route', 'status', 'action'])
                ->make(true);
        }

        return view('warehouse.transfers.monitor');
    }

    public function show($id)
    {
        $transfer = StockTransfer::with(['fromStore', 'toStore', 'product'])->findOrFail($id);

        $html = view('warehouse.transfers._modal_content', compact('transfer'))->render();

        return response()->json(['html' => $html]);
    }
}
