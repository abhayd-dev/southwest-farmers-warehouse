<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

/**
 * Everything the Purchase Orders list screen needs: the summary cards and the
 * server-side DataTable feed (including how each status is displayed).
 */
class PurchaseOrderListing
{
    public function stats(): array
    {
        $byStatus = PurchaseOrder::selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('status')->get()
            ->pluck('count', 'status')->toArray();

        return [
            'total' => array_sum($byStatus),
            'pending' => ($byStatus[PurchaseOrder::STATUS_ORDERED] ?? 0) + ($byStatus[PurchaseOrder::STATUS_PARTIAL] ?? 0),
            'completed' => $byStatus[PurchaseOrder::STATUS_COMPLETED] ?? 0,
            'value' => number_format(PurchaseOrder::sum('total_amount'), 0),
            'by_status' => $byStatus,
        ];
    }

    public function dataTable(Request $request)
    {
        $query = PurchaseOrder::with('vendor', 'creator')->latest()->forListTab($request->input('status'));

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
            ->addColumn('vendor_name', fn ($row) => optional($row->vendor)->name ?? 'N/A')
            ->editColumn('order_date', fn ($row) => $row->order_date ? Carbon::parse($row->order_date)->format('d M Y') : '-')
            ->addColumn('total_amount', fn ($row) => '$ ' . number_format($row->total_amount, 2))
            ->addColumn('progress', fn ($row) => $this->progressBar($row))
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['progress', 'status_badge', 'action'])
            ->make(true);
    }

    private function progressBar(PurchaseOrder $row): string
    {
        $color = $row->progress == 100 ? 'success' : 'primary';

        return '<div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-' . $color . '" role="progressbar" style="width: ' . $row->progress . '%"></div>
                            </div>
                            <small class="text-muted">' . $row->progress . '% Received</small>';
    }

    private function statusBadge(PurchaseOrder $row): string
    {
        if ($row->status === PurchaseOrder::STATUS_COMPLETED && $row->progress < 100) {
            return '<span class="badge rounded-pill text-uppercase px-3 py-2" style="background-color: purple; color: white; font-size: 0.8rem;">' . ($row->shipment_type === PurchaseOrder::SHIPMENT_TRUCK ? 'CLOSED (SHORT)' : 'PARTIAL COMPLETED') . '</span>';
        }

        $displayStatus = strtoupper($row->status);
        $color = 'secondary';

        // Rejected only while the PO is still sitting in draft -- a few older
        // POs were rejected and later went on to be completed or cancelled,
        // and those should show where they actually ended up.
        if ($row->approval_status === PurchaseOrder::APPROVAL_REJECTED && $row->status === PurchaseOrder::STATUS_DRAFT) {
            $displayStatus = 'REJECTED';
            $color = 'danger';
        } elseif ($row->status === PurchaseOrder::STATUS_DRAFT) {
            if ($row->approval_status === PurchaseOrder::APPROVAL_PENDING) {
                $displayStatus = 'WAITING FOR APPROVAL';
                $color = 'warning';
            } elseif ($row->approval_status === PurchaseOrder::APPROVAL_APPROVED) {
                $displayStatus = 'APPROVED';
                $color = 'primary';
            } else {
                $displayStatus = 'DRAFT';
                $color = 'secondary';
            }
        } elseif ($row->status === PurchaseOrder::STATUS_ORDERED) {
            $color = 'info';
        } elseif ($row->status === PurchaseOrder::STATUS_PARTIAL) {
            $displayStatus = 'IN TRANSIT';
            $color = 'warning';
        } elseif ($row->status === PurchaseOrder::STATUS_COMPLETED) {
            $color = 'success';
        } elseif ($row->status === PurchaseOrder::STATUS_CANCELLED) {
            $color = 'danger';
        }

        $badge = '<span class="badge bg-' . $color . ' rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">' . $displayStatus . '</span>';

        // Client PDF 9/24, item 1: over-receipt waiting on the approver.
        if ($row->over_receipt_status === PurchaseOrder::OVER_RECEIPT_PENDING) {
            $badge .= '<br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.7rem;">OVER-RECEIPT: NEEDS APPROVAL</span>';
        }

        return $badge;
    }

    private function actionButtons(PurchaseOrder $row): string
    {
        $viewUrl = route('warehouse.purchase-orders.show', $row->id);

        return '<div class="action-btns">
                                <a href="' . $viewUrl . '" class="btn btn-sm btn-outline-info btn-view" title="View">
                                    <i class="mdi mdi-eye"></i> View
                                </a>
                            </div>';
    }
}
