<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Pallet;
use App\Models\PalletItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StorePurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PalletController extends Controller
{
    const MAX_PALLET_WEIGHT = 2200; // lbs

    /**
     * List all pallets (Pallet Builder Dashboard)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'preparing');

        $pallets = Pallet::with(['items.product', 'department', 'storePO.store'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $preparingCount  = Pallet::where('status', Pallet::STATUS_PREPARING)->count();
        $readyCount      = Pallet::where('status', Pallet::STATUS_READY)->count();
        $inTransitCount  = Pallet::where('status', Pallet::STATUS_IN_TRANSIT)->count();
        $deliveredCount  = Pallet::where('status', Pallet::STATUS_DELIVERED)->count();

        return view('warehouse.pallets.index', compact(
            'pallets', 'status',
            'preparingCount', 'readyCount', 'inTransitCount', 'deliveredCount'
        ));
    }

    /**
     * Show form to create a new pallet (optionally linked to a Store PO)
     */
    public function create(Request $request)
    {
        $departments = Department::where('is_active', true)->get();
        $pendingPOs  = StorePurchaseOrder::with('store')
            ->where('status', 'approved')
            ->latest()
            ->get();

        $selectedPoId = $request->get('po_id');

        return view('warehouse.pallets.create', compact('departments', 'pendingPOs', 'selectedPoId'));
    }

    /**
     * Store a new pallet
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'store_po_id'   => 'nullable|exists:store_purchase_orders,id',
            'max_weight'    => 'nullable|numeric|min:1|max:' . self::MAX_PALLET_WEIGHT,
            'notes'         => 'nullable|string|max:500',
        ]);

        $pallet = Pallet::create([
            'pallet_number' => Pallet::generatePalletNumber(),
            'department_id' => $request->department_id,
            'store_po_id'   => $request->store_po_id,
            'max_weight'    => $request->max_weight ?? self::MAX_PALLET_WEIGHT,
            'total_weight'  => 0,
            'status'        => Pallet::STATUS_PREPARING,
            'notes'         => $request->notes,
        ]);

        return redirect()->route('warehouse.pallets.show', $pallet->id)
            ->with('success', "Pallet {$pallet->pallet_number} created. Now add items.");
    }

    /**
     * Show pallet detail / builder screen
     */
    public function show(Pallet $pallet)
    {
        $pallet->load(['items.product', 'department', 'storePO.store.storePO.items.product']);

        // Products available to add (from warehouse stock)
        $products = Product::where('is_active', true)
            ->whereHas('stock', fn($q) => $q->where('quantity', '>', 0))
            ->with('stock')
            ->orderBy('product_name')
            ->get();

        $weightPercent = $pallet->max_weight > 0
            ? min(100, round(($pallet->total_weight / $pallet->max_weight) * 100))
            : 0;

        return view('warehouse.pallets.show', compact('pallet', 'products', 'weightPercent'));
    }

    /**
     * Add an item to a pallet (AJAX)
     */
    public function addItem(Request $request, Pallet $pallet)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'quantity'        => 'required|integer|min:1',
            'weight_per_unit' => 'required|numeric|min:0.01',
        ]);

        if ($pallet->status !== Pallet::STATUS_PREPARING) {
            return response()->json(['success' => false, 'message' => 'Pallet is no longer in preparing state.'], 422);
        }

        $totalWeight = $request->quantity * $request->weight_per_unit;

        // Check weight limit
        if (($pallet->total_weight + $totalWeight) > $pallet->max_weight) {
            $remaining = $pallet->max_weight - $pallet->total_weight;
            return response()->json([
                'success' => false,
                'message' => "Exceeds weight limit! Remaining capacity: " . number_format($remaining, 2) . " lbs"
            ], 422);
        }

        // Check warehouse stock
        $warehouseQty = ProductStock::where('product_id', $request->product_id)
            ->where('warehouse_id', 1)
            ->sum('quantity');

        if ($warehouseQty < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient warehouse stock. Available: {$warehouseQty}"
            ], 422);
        }

        DB::transaction(function () use ($pallet, $request, $totalWeight) {
            PalletItem::create([
                'pallet_id'       => $pallet->id,
                'product_id'      => $request->product_id,
                'quantity'        => $request->quantity,
                'weight_per_unit' => $request->weight_per_unit,
                'total_weight'    => $totalWeight,
            ]);

            $pallet->increment('total_weight', $totalWeight);
        });

        $pallet->refresh();
        $weightPercent = min(100, round(($pallet->total_weight / $pallet->max_weight) * 100));

        return response()->json([
            'success'        => true,
            'message'        => 'Item added to pallet.',
            'total_weight'   => number_format($pallet->total_weight, 2),
            'remaining'      => number_format($pallet->remainingCapacity(), 2),
            'weight_percent' => $weightPercent,
        ]);
    }

    /**
     * Remove an item from a pallet (AJAX)
     */
    public function removeItem(PalletItem $item)
    {
        $pallet = $item->pallet;

        if ($pallet->status !== Pallet::STATUS_PREPARING) {
            return response()->json(['success' => false, 'message' => 'Cannot modify a pallet that is not in preparing state.'], 422);
        }

        DB::transaction(function () use ($pallet, $item) {
            $pallet->decrement('total_weight', $item->total_weight);
            $item->delete();
        });

        $pallet->refresh();
        $weightPercent = $pallet->max_weight > 0
            ? min(100, round(($pallet->total_weight / $pallet->max_weight) * 100))
            : 0;

        return response()->json([
            'success'        => true,
            'message'        => 'Item removed.',
            'total_weight'   => number_format($pallet->total_weight, 2),
            'remaining'      => number_format($pallet->remainingCapacity(), 2),
            'weight_percent' => $weightPercent,
        ]);
    }

    /**
     * Mark pallet as Ready for dispatch
     */
    public function markReady(Pallet $pallet)
    {
        if ($pallet->items->isEmpty()) {
            return back()->with('error', 'Cannot mark an empty pallet as ready.');
        }

        if ($pallet->isOverweight()) {
            return back()->with('error', "Pallet exceeds weight limit ({$pallet->max_weight} lbs).");
        }

        $pallet->update(['status' => Pallet::STATUS_READY]);

        return back()->with('success', "Pallet {$pallet->pallet_number} marked as Ready for dispatch.");
    }

    /**
     * Print pallet manifest
     */
    public function printManifest(Pallet $pallet)
    {
        $pallet->load(['items.product', 'department', 'storePO.store']);
        return view('warehouse.pallets.manifest', compact('pallet'));
    }
}
