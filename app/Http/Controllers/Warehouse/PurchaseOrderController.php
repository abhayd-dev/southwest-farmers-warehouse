<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\ProductBatch;
use App\Models\Vendor;
use App\Models\Product;
use App\Services\NotificationService;
use App\Services\PurchaseOrderService;
use App\Services\ApprovalService;
use App\Services\VendorCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    protected $poService;
    protected $approvalService;
    protected $vendorService;

    public function __construct(
        PurchaseOrderService $poService,
        ApprovalService $approvalService,
        VendorCommunicationService $vendorService
    ) {
        $this->poService = $poService;
        $this->approvalService = $approvalService;
        $this->vendorService = $vendorService;
    }

    public function index(Request $request)
    {
        // Stats endpoint for summary cards
        if ($request->filled('stats')) {
            $all = PurchaseOrder::selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')->groupBy('status')->get();
            $byStatus = $all->pluck('count', 'status')->toArray();
            return response()->json([
                'stats' => [
                    'total'     => array_sum($byStatus),
                    'pending'   => ($byStatus['ordered'] ?? 0) + ($byStatus['partial'] ?? 0),
                    'completed' => $byStatus['completed'] ?? 0,
                    'value'     => number_format(PurchaseOrder::sum('total_amount'), 0),
                    'by_status' => $byStatus,
                ]
            ]);
        }

        if ($request->ajax()) {
            $query = PurchaseOrder::with('vendor', 'creator')->latest();

            if ($request->filled('status') && $request->status !== 'all') {
                $status = $request->status;
                if ($status === 'in_transit') {
                    $status = 'partial'; // Mapping for UI
                }
                $query->where('status', $status);
            }
            if ($request->filled('approval_status') && $request->approval_status !== 'all') {
                $query->where('approval_status', $request->approval_status);
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
                ->addColumn('vendor_name', fn($row) => $row->vendor->name)
                ->editColumn('order_date', function ($row) {
                    return $row->order_date ? Carbon::parse($row->order_date)->format('d M Y') : '-';
                })
                ->addColumn('total_amount', fn($row) => '$ ' . number_format($row->total_amount, 2))
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
                        'draft' => 'secondary',
                        'ordered' => 'info',
                        'partial' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $displayStatus = strtoupper($row->status);
                    if ($row->status === 'partial') {
                        $displayStatus = 'IN TRANSIT';
                    }
                    return '<span class="badge bg-' . ($badges[$row->status] ?? 'secondary') . ' rounded-pill text-uppercase px-3 py-2" style="font-size: 0.8rem;">' . $displayStatus . '</span>';
                })
                ->addColumn('approval_badge', function ($row) {
                    $badges = ['approved' => 'success', 'rejected' => 'danger'];

                    if ($row->approval_status === 'approved' || $row->approval_status === 'rejected') {
                        $color = $badges[$row->approval_status] ?? 'secondary';
                        $icon = $row->approval_status === 'approved' ? '✓' : '✗';
                        return '<span class="badge bg-' . $color . ' px-2 py-1">' . $icon . ' ' . strtoupper($row->approval_status) . '</span>';
                    }

                    return ''; // Do not show N/A or pending per client request
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('warehouse.purchase-orders.show', $row->id);
                    return '<div class="action-btns">
                                <a href="' . $viewUrl . '" class="btn btn-sm btn-outline-info btn-view" title="View / Receive">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['progress', 'status_badge', 'approval_badge', 'action'])
                ->make(true);
        }

        return view('warehouse.purchase-orders.index');
    }

    /**
     * Receiving History — show all batches received for this PO
     */
    public function receivingHistory(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product']);

        $productIds = $purchaseOrder->items->pluck('product_id');

        $batches = ProductBatch::with('product')
            ->whereIn('product_id', $productIds)
            ->where('warehouse_id', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('warehouse.purchase-orders.receiving-history', compact('purchaseOrder', 'batches'));
    }

    public function create()
    {
        $vendors = Vendor::active()->get();
        // Only get Warehouse Products
        $products = Product::warehouse()->active()->select('id', 'product_name', 'sku', 'barcode', 'cost_price')->get();

        return view('warehouse.purchase-orders.create', compact('vendors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        try {
            $po = $this->poService->createPO($request->all());

            // Send approval email if provided
            if ($request->filled('approval_email')) {
                try {
                    $this->approvalService->sendApprovalEmail($po);
                    $approvalMessage = ' Approval email sent to ' . $request->approval_email;
                } catch (\Exception $e) {
                    Log::error('Failed to send approval email: ' . $e->getMessage());
                    $approvalMessage = ' (Note: Approval email failed to send)';
                }
            } else {
                $approvalMessage = '';
            }

            NotificationService::sendToAdmins(
                'New PO Created',
                "PO #{$po->po_number} created by " . auth()->user()->name . $approvalMessage,
                'info',
                route('warehouse.purchase-orders.show', $po->id)
            );

            return redirect()->route('warehouse.purchase-orders.show', $po->id)
                ->with('success', 'Purchase Order created successfully!' . $approvalMessage);
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating PO: ' . $e->getMessage());
        }
    }

    /**
     * Create a DRAFT PO from Restock Planning data
     */
    public function bulkStoreDraft(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        try {
            // Find a common vendor if possible, otherwise use a placeholder or ask
            // For now, we'll assign to the first available vendor or the last vendor of the product
            // Alternatively, redirects to 'create' page with pre-filled items

            // Actually, let's just redirect to the Create PO page with the items in the session
            // so the user can select the vendor and verify costs.
            return redirect()->route('warehouse.purchase-orders.create')
                ->with('prefilled_items', $request->items);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process restock items: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product', 'creator']);
        return view('warehouse.purchase-orders.show', compact('purchaseOrder'));
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('approve_po')) {
            abort(403, 'Unauthorized');
        }
        if ($purchaseOrder->status !== 'draft') abort(403);

        $purchaseOrder->update(['status' => 'ordered']);

        NotificationService::sendToAdmins(
            'PO Ordered',
            "PO #{$purchaseOrder->po_number} marked as ordered.",
            'success',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );
        return back()->with('success', 'PO marked as Ordered. Sent to Vendor.');
    }

    public function markCompleted(PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receive_po')) {
            abort(403, 'Unauthorized');
        }
        if ($purchaseOrder->status !== 'partial') abort(403);

        $purchaseOrder->update(['status' => 'completed']);

        NotificationService::sendToAdmins(
            'PO Completed',
            "PO #{$purchaseOrder->po_number} marked as completed manually.",
            'success',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );
        return back()->with('success', 'PO marked as Completed (Partially Received).');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        set_time_limit(300);

        $request->validate([
            'invoice_number' => 'required|string',
            'items' => 'required|array',
        ]);

        try {
            $this->poService->receiveItems($purchaseOrder->id, $request->items, $request->invoice_number);

            NotificationService::sendToAdmins(
                'Stock Received',
                "Received items for PO #{$purchaseOrder->po_number}. Status: " . ucfirst($purchaseOrder->status),
                'success',
                route('warehouse.purchase-orders.show', $purchaseOrder->id)
            );
            return back()->with('success', 'Stock received successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Receive failed: ' . $e->getMessage());
        }
    }

    public function printLabels(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.product', 'vendor']);

        $items = $purchaseOrder->items->filter(function ($item) {
            return $item->received_quantity > 0;
        });

        if ($items->isEmpty()) {
            return back()->with('error', 'No received items found to print labels for.');
        }

        // Barcode Generator Instance
        $generator = new BarcodeGeneratorPNG();

        $pdf = Pdf::loadView('warehouse.purchase-orders.labels', compact('purchaseOrder', 'items', 'generator'));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('PO-' . $purchaseOrder->po_number . '-Labels.pdf');
    }

    /**
     * Print PO as PDF
     */
    public function printPO(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product']);

        // Get warehouse details from database
        // Try to get from PO relationship, then first warehouse, then null (will use config fallback in view)
        $warehouse = null;
        if ($purchaseOrder->warehouse_id) {
            $warehouse = $purchaseOrder->warehouse;
        }
        if (!$warehouse) {
            $warehouse = \App\Models\Warehouse::first();
        }

        $pdf = Pdf::loadView('warehouse.purchase-orders.print', [
            'po' => $purchaseOrder,
            'warehouse' => $warehouse
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('PO-' . $purchaseOrder->po_number . '.pdf');
    }

    /**
     * Send PO to vendor via email/SMS
     */
    public function sendToVendor(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'send_email' => 'sometimes|boolean',
            'send_sms' => 'sometimes|boolean',
        ]);

        $sendEmail = $request->input('send_email', true);
        $sendSMS = $request->input('send_sms', false);

        try {
            $results = $this->vendorService->sendPOToVendor($purchaseOrder, $sendEmail, $sendSMS);

            $messages = [];
            if ($results['email']) {
                $messages[] = 'Email sent successfully';
            }
            if ($results['sms']) {
                $messages[] = 'SMS sent successfully';
            }

            if (!empty($results['errors'])) {
                $errorMsg = implode(', ', $results['errors']);
                return back()->with('warning', 'Partial success: ' . implode(', ', $messages) . '. Errors: ' . $errorMsg);
            }

            if (empty($messages)) {
                return back()->with('error', 'No communication method selected or available');
            }

            // Notify admins
            NotificationService::sendToAdmins(
                'PO Sent to Vendor',
                "PO #{$purchaseOrder->po_number} sent to {$purchaseOrder->vendor->name}",
                'info',
                route('warehouse.purchase-orders.show', $purchaseOrder->id)
            );

            return back()->with('success', 'PO sent to vendor: ' . implode(', ', $messages));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send PO: ' . $e->getMessage());
        }
    }

    /**
     * Handle approval/rejection from email link
     */
    public function handleApproval(Request $request, PurchaseOrder $purchaseOrder)
    {
        $action = $request->query('action'); // 'approve' or 'reject'

        if (!in_array($action, ['approve', 'reject'])) {
            return view('warehouse.purchase-orders.approval-result', [
                'success' => false,
                'message' => 'Invalid action',
            ]);
        }

        // If rejecting, show form to collect reason
        if ($action === 'reject' && !$request->has('reason')) {
            return view('warehouse.purchase-orders.rejection-form', compact('purchaseOrder'));
        }

        try {
            $approverEmail = $purchaseOrder->approval_email;
            $reason = $request->input('reason');

            $message = $this->approvalService->processApproval(
                $purchaseOrder,
                $action,
                $approverEmail,
                $reason
            );

            // Notify warehouse staff
            NotificationService::sendToAdmins(
                'PO ' . ucfirst($action),
                "PO #{$purchaseOrder->po_number} has been {$action}ed by {$approverEmail}",
                $action === 'approve' ? 'success' : 'warning',
                route('warehouse.purchase-orders.show', $purchaseOrder->id)
            );

            return view('warehouse.purchase-orders.approval-result', [
                'success' => true,
                'message' => $message,
                'po' => $purchaseOrder,
            ]);
        } catch (\Exception $e) {
            return view('warehouse.purchase-orders.approval-result', [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
