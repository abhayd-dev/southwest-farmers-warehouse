<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PurchaseOrder::with(['vendor', 'items'])->whereIn('status', [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIAL,
                PurchaseOrder::STATUS_COMPLETED
            ])->latest();

            if ($request->filled('status') && $request->status !== 'all') {
                $status = $request->status;
                if ($status === 'in_transit') {
                    $status = 'partial'; // UI maps In Transit to partial
                }
                $query->where('status', $status);
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
                        return '<span class="badge rounded-pill text-uppercase px-3 py-2" style="background-color: purple; color: white; font-size: 0.8rem;">PARTIAL COMPLETED</span>';
                    }
                    $badges = [
                        'ordered' => 'info',
                        'partial' => 'warning',
                        'completed' => 'success'
                    ];
                    $displayStatus = strtoupper($row->status);
                    if ($row->status === 'partial') {
                        $displayStatus = 'IN TRANSIT';
                    }
                    return '<span class="badge bg-' . ($badges[$row->status] ?? 'secondary') . ' rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">' . $displayStatus . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('warehouse.purchase-orders.show', $row->id) . '?source=receiving';
                    return '<div class="action-btns">
                                <a href="' . $viewUrl . '" class="btn btn-sm btn-primary btn-view" title="Receive / View">
                                    <i class="mdi mdi-truck-check"></i> Receive
                                </a>
                            </div>';
                })
                ->rawColumns(['progress', 'status_badge', 'action'])
                ->make(true);
        }

        return view('warehouse.receiving.index');
    }

    public function receipt(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product']);

        $warehouse = \App\Models\Warehouse::first();

        $pdf = Pdf::loadView('warehouse.receiving.receipt', [
            'po' => $purchaseOrder,
            'warehouse' => $warehouse
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Receipt-PO-' . $purchaseOrder->po_number . '.pdf');
    }
}
