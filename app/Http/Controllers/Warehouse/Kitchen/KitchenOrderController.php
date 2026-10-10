<?php

namespace App\Http\Controllers\Warehouse\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

/**
 * Warehouse view of the kitchen board, same rules as the store KDS (kitchen
 * spec 5.2): Incoming -> Accepted -> Preparing -> Ready -> Handoff ->
 * Completed, one step at a time, cancel needs a reason. Only orders with
 * kitchen menu items (it used to list every sale of every store).
 */
class KitchenOrderController extends Controller
{
    public const NEXT = [
        'New' => 'Accepted',
        'Accepted' => 'Preparing',
        'Preparing' => 'Ready',
        'Ready' => 'Handoff',
        'Handoff' => 'Completed',
    ];

    public const CANCEL_REASONS = ['Customer cancelled', 'Item unavailable / sold out', 'Kitchen closed', 'Duplicate order', 'Other'];

    public function index(Request $request)
    {
        $orders = Sale::with('items.product', 'items.menuItem', 'store')
            ->whereHas('items', fn ($q) => $q->whereNotNull('menu_item_id'))
            ->whereNotIn('kitchen_status', ['Completed', 'Cancelled'])
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')))
            // A website card order isn't real until paid (these columns come from the store app's schema).
            ->when(\Illuminate\Support\Facades\Schema::hasColumns('sales', ['source', 'status', 'payment_status']), fn ($query) => $query->where(
                fn ($q) => $q->whereRaw("LOWER(COALESCE(payment_method, '')) <> 'card'")
                    ->orWhere('source', '!=', 'website')->orWhereNull('source')
                    ->orWhere('payment_status', 'completed')->orWhere('status', 'paid')))
            ->orderByRaw('COALESCE(due_at, created_at) ASC')
            ->get();

        $kanbanData = collect(array_keys(self::NEXT))
            ->mapWithKeys(fn ($status) => [$status => $orders->where('kitchen_status', $status)->values()])
            ->all();
        $cancelReasons = self::CANCEL_REASONS;

        return view('warehouse.kitchen.kds', compact('kanbanData', 'cancelReasons'));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'status' => 'required|in:Accepted,Preparing,Ready,Handoff,Completed,Cancelled',
            'reason' => 'required_if:status,Cancelled|nullable|string|max:255',
        ], ['reason.required_if' => 'Choose a reason for cancelling.']);

        $current = $sale->kitchen_status ?: 'New';
        $target = $request->status;
        $allowed = $target === 'Cancelled'
            ? array_key_exists($current, self::NEXT)
            : (self::NEXT[$current] ?? null) === $target;
        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => "Order #{$sale->invoice_number} is {$current}; it can't move to {$target}. Refresh the board.",
            ], 422);
        }

        $sale->update([
            'kitchen_status' => $target,
            'kitchen_cancel_reason' => $target === 'Cancelled' ? $request->reason : $sale->kitchen_cancel_reason,
            'kitchen_status_changed_at' => now(),
            'kitchen_status_changed_by' => auth()->user()->name ?? null,
        ]);

        return response()->json(['success' => true, 'message' => "Order #{$sale->invoice_number} is now {$target}."]);
    }
}
