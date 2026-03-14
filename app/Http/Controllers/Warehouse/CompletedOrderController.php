<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class CompletedOrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PurchaseOrder::with(['vendor', 'items'])
                ->whereIn('status', ['completed', 'cancelled'])
                ->latest();

            // Handle "partial completed" which means status is completed but progress < 100
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'partially_completed') {
                    $query->where('status', 'completed')->where('progress', '<', 100);
                } elseif ($request->status === 'completed') {
                    $query->where('status', 'completed')->where('progress', '>=', 100);
                } else {
                    $query->where('status', $request->status);
                }
            }

            if ($request->filled('po_number')) {
                $query->where('po_number', 'like', '%' . $request->po_number . '%');
            }
            if ($request->filled('vendor')) {
                $query->whereHas('vendor', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->vendor . '%');
                });
            }
            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('po_number', fn($row) => $row->po_number)
                ->addColumn('vendor_name', fn($row) => $row->vendor->name)
                ->editColumn('order_date', function ($row) {
                    return $row->order_date ? Carbon::parse($row->order_date)->format('d M Y') : '-';
                })
                ->addColumn('receiving_date', function ($row) {
                    return $row->updated_at ? Carbon::parse($row->updated_at)->format('d M Y') : '-';
                })
                ->addColumn('po_amount', fn($row) => '$ ' . number_format($row->total_amount, 2))
                ->addColumn('receiving_amount', function ($row) {
                    $receivedAmount = $row->items->sum(function ($item) {
                        return $item->received_quantity * $item->unit_cost;
                    });
                    return '$ ' . number_format($receivedAmount, 2);
                })
                ->addColumn('progress', function ($row) {
                    $color = $row->progress == 100 ? 'success' : 'primary';
                    return '<div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-' . $color . '" role="progressbar" style="width: ' . $row->progress . '%"></div>
                            </div>
                            <small class="text-muted">' . $row->progress . '% Received</small>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'completed' && $row->progress < 100) {
                        return '<span class="badge rounded-pill text-uppercase px-3 py-2" style="background-color: purple; color: white; font-size: 0.8rem;">PARTIALLY COMPLETED</span>';
                    } elseif ($row->status === 'cancelled') {
                        return '<span class="badge bg-danger rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">CANCELLED</span>';
                    } elseif ($row->status === 'completed') {
                        return '<span class="badge bg-success rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">COMPLETED</span>';
                    }

                    return '<span class="badge bg-warning rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">IN TRANSIT</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('warehouse.purchase-orders.show', $row->id);
                    return '<div class="action-btns">
                                <a href="' . $viewUrl . '" class="btn btn-sm btn-outline-primary btn-view" title="View Details">
                                    <i class="mdi mdi-eye"></i> View
                                </a>
                            </div>';
                })
                ->rawColumns(['progress', 'status_badge', 'action'])
                ->make(true);
        }

        return view('warehouse.completed-orders.index');
    }
}
