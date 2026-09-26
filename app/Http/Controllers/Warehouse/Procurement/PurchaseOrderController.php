<?php

namespace App\Http\Controllers\Warehouse\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BulkDraftPurchaseOrderRequest;
use App\Http\Requests\Warehouse\ReceivePurchaseOrderRequest;
use App\Http\Requests\Warehouse\SavePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\ProductBatch;
use App\Models\Vendor;
use App\Models\Product;
use App\Services\NotificationService;
use App\Services\PurchaseOrderService;
use App\Services\ApprovalService;
use App\Services\PurchaseOrderListing;
use App\Services\VendorCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

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

    /** 403 unless the user holds at least one of the permissions (Super Admin always passes). */
    private function authorizeAnyOf(string ...$permissions): void
    {
        abort_unless(
            collect($permissions)->contains(fn ($permission) => auth()->user()->can($permission)),
            403,
            'Unauthorized'
        );
    }

    public function index(Request $request, PurchaseOrderListing $listing)
    {
        // Summary cards
        if ($request->filled('stats')) {
            return response()->json(['stats' => $listing->stats()]);
        }

        // DataTable feed
        if ($request->ajax()) {
            return $listing->dataTable($request);
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
        
        $departments = \App\Models\Department::where('is_active', true)->get();
        $categories = \App\Models\ProductCategory::where('is_active', true)->get();
        $subcategories = \App\Models\ProductSubcategory::where('is_active', true)->get();

        // Only get Warehouse Products
        $products = Product::warehouse()->active()
            ->select('id', 'product_name', 'sku', 'barcode', 'cost_price', 'department_id', 'category_id', 'subcategory_id')
            ->get();

        return view('warehouse.purchase-orders.create', compact('vendors', 'products', 'departments', 'categories', 'subcategories'));
    }

    public function store(SavePurchaseOrderRequest $request)
    {
        try {
            $po = $this->poService->createPO($request->all());

            NotificationService::sendToAdmins(
                'New PO Created',
                "PO #{$po->po_number} created by " . auth()->user()->name,
                'info',
                route('warehouse.purchase-orders.show', $po->id)
            );

            return redirect()->route('warehouse.purchase-orders.show', $po->id)
                ->with('success', 'Purchase Order saved as draft successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating PO: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.'));
        }
    }

    /**
     * Create a DRAFT PO from Restock Planning data
     */
    public function bulkStoreDraft(BulkDraftPurchaseOrderRequest $request)
    {
        try {
            // Find a common vendor if possible, otherwise use a placeholder or ask
            // For now, we'll assign to the first available vendor or the last vendor of the product
            // Alternatively, redirects to 'create' page with pre-filled items

            // Actually, let's just redirect to the Create PO page with the items in the session
            // so the user can select the vendor and verify costs.
            return redirect()->route('warehouse.purchase-orders.create')
                ->with('prefilled_items', $request->items);
        } catch (\Exception $e) {
            Log::error('Failed to process restock items: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.'));
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product', 'creator']);
        return view('warehouse.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Revert an approved or submitted PO back to DRAFT status
     */
    public function revertToDraft(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_COMPLETED, 'received'])) {
            return back()->with('error', 'Cannot revert a completed or received purchase order.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DRAFT]);

        NotificationService::sendToAdmins(
            'PO Reverted to Draft',
            "PO #{$purchaseOrder->po_number} was reverted to draft status by " . auth()->user()->name,
            'warning',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );

        return redirect()->route('warehouse.purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Purchase order status reset to draft. You can now edit, cancel, or resubmit for approval.');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        $vendors = Vendor::active()->get();
        $products = Product::warehouse()->active()->select('id', 'product_name', 'sku', 'barcode', 'cost_price')->get();
        $purchaseOrder->load(['items.product']);

        return view('warehouse.purchase-orders.edit', compact('purchaseOrder', 'vendors', 'products'));
    }

    public function update(SavePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        try {
            $this->poService->updatePO($purchaseOrder, $request->all());

            return redirect()->route('warehouse.purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating PO: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.'));
        }
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAnyOf('approve_po');
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) abort(403);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_ORDERED]);

        NotificationService::sendToAdmins(
            'PO Ordered',
            "PO #{$purchaseOrder->po_number} marked as ordered.",
            'info',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );

        return back()->with('success', 'Purchase Order marked as Ordered successfully.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAnyOf('approve_po');
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_DRAFT])) abort(403);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return back()->with('success', 'Purchase Order has been cancelled.');
    }

    public function sendApproval(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAnyOf('approve_po', 'create_po');

        try {
            if (!$purchaseOrder->approval_email) {
                return back()->with('error', 'No approval email set for this PO. Please edit the PO to add one.');
            }
            
            // Update status first so it changes even if email fails
            $purchaseOrder->update(['approval_status' => PurchaseOrder::APPROVAL_PENDING]);
            
            $this->approvalService->sendApprovalEmail($purchaseOrder);
            
            return back()->with('success', 'Approval email sent successfully to ' . $purchaseOrder->approval_email);
        } catch (\Exception $e) {
            Log::error('Failed to send approval email: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Approval email to ' . $purchaseOrder->approval_email . ' failed: ' . \App\Support\MailFailure::reason($e));
        }
    }

    public function markCompleted(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAnyOf('receive_po');
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_PARTIAL) abort(403);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

        NotificationService::sendToAdmins(
            'PO Completed',
            "PO #{$purchaseOrder->po_number} marked as completed manually.",
            'success',
            route('warehouse.purchase-orders.show', $purchaseOrder->id)
        );
        return back()->with('success', 'PO marked as Completed (Partially Received).');
    }

    public function receive(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        set_time_limit(300);

        try {
            $invoiceDocumentPath = null;
            if ($request->hasFile('invoice_document')) {
                $disk = config('filesystems.disks.r2') ? 'r2' : 'public';
                $file = $request->file('invoice_document');
                $filename = 'invoice_' . $purchaseOrder->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $invoiceDocumentPath = $file->storeAs('invoices', $filename, $disk);
            }

            $this->poService->receiveItems(
                $purchaseOrder->id,
                $request->items,
                $request->invoice_number,
                $request->input('duties', 0),
                $request->input('shipping_cost', 0),
                $request->input('taxes', 0),
                $request->input('transportation_cost', 0),
                $request->input('demurrage', 0),
                $invoiceDocumentPath,
                $request->input('shipment_type')
            );

            NotificationService::sendToAdmins(
                'Inventory Updated',
                "Stock for PO #{$purchaseOrder->po_number} has been received.",
                'success'
            );

            $purchaseOrder->refresh();
            $message = 'Inventory updated successfully.';
            if ($purchaseOrder->over_receipt_status === PurchaseOrder::OVER_RECEIPT_PENDING) {
                $message .= ' More was received than ordered, so the order has been flagged and sent for approval.';
            }

            return redirect()->route('warehouse.receiving.show', $purchaseOrder->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'CostIncreaseException') {
                $approvalUrl = route('warehouse.purchase-orders.cost-approval', $purchaseOrder->id);
                NotificationService::sendToAdmins(
                    'Cost Increase Approval Required',
                    "PO #{$purchaseOrder->po_number} requires approval due to increased receiving cost.",
                    'warning',
                    $approvalUrl
                );
                return back()->with('error', 'Cannot proceed. True cost is higher than current cost. Sent for approval.');
            }
            
            Log::error('Receive failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.'));
        }
    }

    public function showCostApproval(PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('warehouse.purchase-orders.index')->with('error', 'Unauthorized access.');
        }

        $purchaseOrder->load(['items.product', 'vendor']);

        return view('warehouse.purchase-orders.cost-approval', compact('purchaseOrder'));
    }

    public function approveCostIncrease(PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('warehouse.purchase-orders.index')->with('error', 'Unauthorized access.');
        }

        $purchaseOrder->update(['cost_increase_approved' => true]);

        return redirect()->route('warehouse.receiving.show', $purchaseOrder->id)
            ->with('success', 'Cost increase approved. You can now process the receiving.');
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
        if ($purchaseOrder->approval_status === PurchaseOrder::APPROVAL_REJECTED || $purchaseOrder->status === 'rejected') {
            return back()->with('error', 'Cannot send a rejected Purchase Order to vendor. Please resubmit order for approval first.');
        }

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
                Log::warning('PO communication partial success/errors: ' . implode(', ', $results['errors']));
                return back()->with('warning', 'Partial success: ' . implode(', ', $messages) . '. Something went wrong. Please try again later.');
            }

            if (empty($messages)) {
                return back()->with('error', 'No communication method selected or available');
            }

            // Update status to ordered if it was draft/approved
            if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, 'approved']) || $purchaseOrder->approval_status === PurchaseOrder::APPROVAL_APPROVED) {
                $purchaseOrder->update(['status' => PurchaseOrder::STATUS_ORDERED]);
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
            Log::error('Failed to send PO: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.'));
        }
    }

    /** In-app Approve / Reject of an over-receipt (client PDF 9/24, item 1). */
    public function overReceiptDecision(Request $request, PurchaseOrder $purchaseOrder, \App\Services\OverReceiptService $service)
    {
        $this->authorizeAnyOf('approve_po');
        $request->validate(['decision' => 'required|in:approve,reject']);

        $decided = $service->decide($purchaseOrder, $request->decision, auth()->user()->name ?? auth()->user()->email);

        return back()->with($decided ? 'success' : 'error', $decided
            ? ($request->decision === 'approve' ? 'Over-receipt approved. Invoice updated to the received quantity.' : 'Over-receipt rejected. Invoice kept at the ordered quantity.')
            : 'This over-receipt has already been decided.');
    }
}
