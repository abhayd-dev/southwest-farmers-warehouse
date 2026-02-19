<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMinMaxLevel;
use App\Models\ProductStock;
use App\Models\StoreDetail;
use App\Models\StoreStock;
use App\Models\StorePurchaseOrder;
use App\Models\StorePurchaseOrderItem;
use App\Services\AutoPOGenerationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StorePurchaseOrderController extends Controller
{
    /**
     * List all store purchase orders (PO-based)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = StorePurchaseOrder::with(['store', 'items', 'approver'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('store', fn($s) => $s->where('store_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Stats
        $pendingCount    = StorePurchaseOrder::where('status', 'pending')->count();
        $approvedCount   = StorePurchaseOrder::where('status', 'approved')->count();
        $dispatchedCount = StorePurchaseOrder::where('status', 'dispatched')->count();
        $completedCount  = StorePurchaseOrder::where('status', 'completed')->count();
        $rejectedCount   = StorePurchaseOrder::where('status', 'rejected')->count();

        return view('warehouse.store-orders.index', compact(
            'orders', 'status',
            'pendingCount', 'approvedCount', 'dispatchedCount', 'completedCount', 'rejectedCount'
        ));
    }

    /**
     * Show a single store PO with items
     */
    public function show(StorePurchaseOrder $storeOrder)
    {
        $storeOrder->load(['store', 'items.product.stock', 'creator', 'approver']);

        // For each item, get warehouse stock & in-transit qty
        foreach ($storeOrder->items as $item) {
            $warehouseQty = ProductStock::where('product_id', $item->product_id)
                ->where('warehouse_id', 1)
                ->sum('quantity');

            $inTransitQty = StorePurchaseOrderItem::whereHas('storePurchaseOrder', function ($q) use ($storeOrder) {
                    $q->where('store_id', $storeOrder->store_id)
                      ->whereIn('status', ['approved', 'dispatched']);
                })
                ->where('product_id', $item->product_id)
                ->where('id', '!=', $item->id)
                ->sum('pending_qty');

            $minMax = ProductMinMaxLevel::where('product_id', $item->product_id)->first();

            $item->warehouse_qty  = $warehouseQty;
            $item->in_transit_qty = $inTransitQty;
            $item->min_level      = $minMax?->min_level ?? 0;
            $item->max_level      = $minMax?->max_level ?? 0;
        }

        return view('warehouse.store-orders.show', compact('storeOrder'));
    }

    /**
     * Approve entire PO
     */
    public function approve(Request $request, StorePurchaseOrder $storeOrder)
    {
        if ($storeOrder->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be approved.');
        }

        DB::transaction(function () use ($storeOrder, $request) {
            // Apply rationing logic per item
            // Also check for duplicate open POs per item
            foreach ($storeOrder->items as $item) {
                // ── Duplicate blocking: skip if another open PO has this product for this store ──
                $hasDuplicate = AutoPOGenerationService::hasPendingPO($storeOrder->store_id, $item->product_id)
                    && $item->status === StorePurchaseOrderItem::STATUS_PENDING;

                if ($hasDuplicate) {
                    $item->update([
                        'status'           => StorePurchaseOrderItem::STATUS_REJECTED,
                        'rejection_reason' => 'Duplicate: another open PO already covers this product.',
                        'pending_qty'      => 0,
                    ]);
                    continue;
                }

                $warehouseQty = ProductStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', 1)
                    ->sum('quantity');

                $minMax = ProductMinMaxLevel::where('product_id', $item->product_id)->first();
                $warehouseMin = $minMax?->min_level ?? 0;

                // Rationing: if warehouse stock <= min, dispatch only 25%
                $availableAboveMin = max(0, $warehouseQty - $warehouseMin);

                if ($availableAboveMin <= 0) {
                    // Warehouse at minimum — reject this item
                    $item->update([
                        'status'           => StorePurchaseOrderItem::STATUS_REJECTED,
                        'rejection_reason' => 'Warehouse stock at minimum level.',
                        'pending_qty'      => 0,
                    ]);
                } elseif ($availableAboveMin < $item->requested_qty) {
                    // Rationing: only 25% of requested
                    $rationedQty = max(1, (int) ceil($item->requested_qty * 0.25));
                    $approvedQty = min($rationedQty, $availableAboveMin);
                    $item->update([
                        'status'      => StorePurchaseOrderItem::STATUS_APPROVED,
                        'pending_qty' => $approvedQty,
                    ]);
                } else {
                    $item->update([
                        'status'      => StorePurchaseOrderItem::STATUS_APPROVED,
                        'pending_qty' => $item->requested_qty,
                    ]);
                }
            }

            $storeOrder->approve(Auth::id());

            if ($request->filled('admin_note')) {
                $storeOrder->update(['admin_note' => $request->admin_note]);
            }
        });

        NotificationService::sendToAdmins(
            'Store PO Approved',
            "Store PO #{$storeOrder->po_number} approved by " . Auth::user()->name,
            'success',
            route('warehouse.store-orders.show', $storeOrder->id)
        );

        return back()->with('success', "PO #{$storeOrder->po_number} approved successfully.");
    }

    /**
     * Reject entire PO
     */
    public function reject(Request $request, StorePurchaseOrder $storeOrder)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        if (!in_array($storeOrder->status, ['pending', 'approved'])) {
            return back()->with('error', 'This PO cannot be rejected.');
        }

        $storeOrder->reject(Auth::id(), $request->reason);

        return back()->with('success', "PO #{$storeOrder->po_number} rejected.");
    }

    /**
     * Approve a single item (partial approval)
     */
    public function approveItem(Request $request, StorePurchaseOrderItem $item)
    {
        $request->validate([
            'dispatch_qty' => 'required|integer|min:1',
        ]);

        if ($item->status !== StorePurchaseOrderItem::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Item already processed.'], 422);
        }

        // Stock constraint check
        $warehouseQty = ProductStock::where('product_id', $item->product_id)
            ->where('warehouse_id', 1)
            ->sum('quantity');

        if ($warehouseQty < $request->dispatch_qty) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient warehouse stock. Available: {$warehouseQty}"
            ], 422);
        }

        $item->update([
            'status'      => StorePurchaseOrderItem::STATUS_APPROVED,
            'pending_qty' => $request->dispatch_qty,
        ]);

        return response()->json(['success' => true, 'message' => 'Item approved.']);
    }

    /**
     * Reject a single item
     */
    public function rejectItem(Request $request, StorePurchaseOrderItem $item)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $item->update([
            'status'           => StorePurchaseOrderItem::STATUS_REJECTED,
            'rejection_reason' => $request->reason,
            'pending_qty'      => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Item rejected.']);
    }

    /**
     * Dispatch approved items (deduct from warehouse stock)
     */
    public function dispatch(Request $request, StorePurchaseOrder $storeOrder)
    {
        if ($storeOrder->status !== 'approved') {
            return back()->with('error', 'Only approved POs can be dispatched.');
        }

        $approvedItems = $storeOrder->items->where('status', StorePurchaseOrderItem::STATUS_APPROVED);

        if ($approvedItems->isEmpty()) {
            return back()->with('error', 'No approved items to dispatch.');
        }

        DB::transaction(function () use ($storeOrder, $approvedItems) {
            foreach ($approvedItems as $item) {
                $qtyToDispatch = $item->pending_qty;
                if ($qtyToDispatch <= 0) continue;

                // Deduct from warehouse stock
                $warehouseStock = ProductStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', 1)
                    ->first();

                if (!$warehouseStock || $warehouseStock->quantity < $qtyToDispatch) {
                    throw new \Exception("Insufficient stock for product ID {$item->product_id}. Available: " . ($warehouseStock?->quantity ?? 0));
                }

                $warehouseStock->decrement('quantity', $qtyToDispatch);

                // Update item
                $item->update([
                    'dispatched_qty' => $item->dispatched_qty + $qtyToDispatch,
                    'pending_qty'    => 0,
                    'status'         => StorePurchaseOrderItem::STATUS_DISPATCHED,
                ]);
            }

            // Check if all items dispatched → mark PO as dispatched
            $storeOrder->refresh();
            $allDispatched = $storeOrder->items->every(fn($i) =>
                in_array($i->status, [
                    StorePurchaseOrderItem::STATUS_DISPATCHED,
                    StorePurchaseOrderItem::STATUS_REJECTED,
                ])
            );

            $storeOrder->update(['status' => $allDispatched
                ? StorePurchaseOrder::STATUS_DISPATCHED
                : StorePurchaseOrder::STATUS_APPROVED
            ]);
        });

        NotificationService::sendToAdmins(
            'Store PO Dispatched',
            "Store PO #{$storeOrder->po_number} dispatched to {$storeOrder->store->store_name}",
            'info',
            route('warehouse.store-orders.show', $storeOrder->id)
        );

        return back()->with('success', "Items dispatched for PO #{$storeOrder->po_number}.");
    }

    /**
     * Mark PO as completed (store confirmed receipt)
     */
    public function complete(StorePurchaseOrder $storeOrder)
    {
        if ($storeOrder->status !== 'dispatched') {
            return back()->with('error', 'Only dispatched POs can be marked complete.');
        }

        $storeOrder->update(['status' => StorePurchaseOrder::STATUS_COMPLETED]);

        return back()->with('success', "PO #{$storeOrder->po_number} marked as completed.");
    }

    /**
     * Update admin note on a store PO
     */
    public function updateNote(Request $request, StorePurchaseOrder $storeOrder)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $storeOrder->update(['admin_note' => $request->admin_note]);
        return back()->with('success', 'Admin note saved.');
    }

    /**
     * Manually trigger auto-PO generation for a specific store
     */
    public function generateForStore(StoreDetail $store)
    {
        try {
            $po = AutoPOGenerationService::generateForStore($store);

            if ($po) {
                return redirect()
                    ->route('warehouse.store-orders.show', $po->id)
                    ->with('success', "Auto-generated PO #{$po->po_number} for {$store->store_name} with {$po->items->count()} items.");
            }

            return redirect()
                ->route('warehouse.store-orders.index')
                ->with('info', "No PO needed for {$store->store_name} — stock levels are sufficient or duplicates exist.");
        } catch (\Exception $e) {
            return redirect()
                ->route('warehouse.store-orders.index')
                ->with('error', "Auto-PO generation failed: {$e->getMessage()}");
        }
    }
}
