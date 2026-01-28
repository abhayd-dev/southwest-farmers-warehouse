<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\StoreDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class RecallController extends Controller
{
    // ===== VIEW: MAIN TABS =====
    public function indexTabs()
    {
        return view('warehouse.stock-control.recall.index-tabs');
    }

    // ===== DATA: MY REQUESTS (Warehouse Initiated) =====
    public function myRequests(Request $request)
    {
        // Logic: Initiated by logged-in Warehouse User
        $query = RecallRequest::where('initiated_by', Auth::id())
            ->with(['store', 'product']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('store_id')) $query->where('store_id', $request->store_id);

        return DataTables::of($query)
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . $row->getStatusColor() . '">' . $row->getStatusLabel() . '</span>')
            ->addColumn('action', fn($row) => '<a href="' . route('warehouse.stock-control.recall.show', $row->id) . '" class="btn btn-sm btn-outline-primary">View</a>')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    // ===== DATA: STORE REQUESTS (Store Initiated) =====
    public function storeRequests(Request $request)
    {
        // Logic: Initiated by someone else (Store User), OR specifically pending warehouse approval
        $query = RecallRequest::where('initiated_by', '!=', Auth::id())
            ->with(['store', 'product']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('store_id')) $query->where('store_id', $request->store_id);

        return DataTables::of($query)
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . $row->getStatusColor() . '">' . $row->getStatusLabel() . '</span>')
            ->addColumn('action', fn($row) => '<a href="' . route('warehouse.stock-control.recall.show', $row->id) . '" class="btn btn-sm btn-outline-primary">View</a>')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    // ===== DATA: EXPIRY REPORT =====
    public function expiryDamage(Request $request)
    {
        // Shows warehouse batches
        $query = ProductBatch::query()
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->select([
                'product_batches.*',
                'products.product_name',
                'products.sku',
                DB::raw('(product_batches.expiry_date - CURRENT_DATE) as days_left')
            ])
            ->where('product_batches.warehouse_id', 1)
            ->where(function ($q) {
                $q->where('product_batches.damaged_quantity', '>', 0)
                    ->orWhere('product_batches.expiry_date', '<=', now()->addDays(90));
            });

        return DataTables::of($query)
            ->addColumn('status', fn($row) => $row->days_left <= 0 ? '<span class="badge bg-danger">Expired</span>' : '<span class="badge bg-warning">Warning</span>')
            ->rawColumns(['status'])
            ->make(true);
    }

    // ===== CREATE (Warehouse -> Store) =====
    public function create()
    {
        $stores = StoreDetail::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('warehouse.stock-control.recall.create', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'product_id' => 'required',
            'requested_quantity' => 'required|min:1',
            'reason' => 'required'
        ]);

        RecallRequest::create([
            'store_id' => $request->store_id,
            'product_id' => $request->product_id,
            'requested_quantity' => $request->requested_quantity,
            'reason' => $request->reason,
            'reason_remarks' => $request->reason_remarks,
            'initiated_by' => Auth::id(),
            'status' => RecallRequest::STATUS_PENDING_STORE_APPROVAL,
        ]);

        
        return redirect()->route('warehouse.stock-control.recall')->with('success', 'Recall request sent to Store.');
    }

    // ===== SHOW DETAILS =====
    public function show($id)
    {
        $recall = RecallRequest::with(['store', 'product'])->findOrFail($id);
        return view('warehouse.stock-control.recall.show', compact('recall'));
    }

    // ===== ACTION: APPROVE (For Store Request) =====
    public function approve(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'approved_quantity' => 'required|integer|min:1|lte:' . $recall->requested_quantity,
            'warehouse_remarks' => 'nullable|string',
        ]);

        // Logic: Warehouse approves -> Status becomes 'approved' -> Store can now Dispatch
        $recall->update([
            'approved_quantity' => $request->approved_quantity,
            'warehouse_remarks' => $request->warehouse_remarks,
            'status' => RecallRequest::STATUS_APPROVED, // Matches your requirement
        ]);

        return back()->with('success', 'Request Approved. Waiting for Store to Dispatch.');
    }

    // ===== ACTION: REJECT =====
    public function reject(Request $request, RecallRequest $recall)
    {
        $request->validate(['warehouse_remarks' => 'required|string']);

        $recall->update([
            'warehouse_remarks' => $request->warehouse_remarks,
            'status' => RecallRequest::STATUS_REJECTED,
        ]);

        return back()->with('success', 'Request Rejected.');
    }

    // ===== ACTION: RECEIVE (Warehouse Receives Stock) =====
    public function receive(Request $request, RecallRequest $recall)
    {
        $request->validate(['received_quantity' => 'required|integer|min:1']);

        DB::transaction(function () use ($request, $recall) {
            // 1. Add Stock to Warehouse Global Stock
            $stock = ProductStock::firstOrCreate(
                ['warehouse_id' => 1, 'product_id' => $recall->product_id],
                ['quantity' => 0]
            );

            // Increment logic
            $stock->increment('quantity', $request->received_quantity);

            // IMPORTANT: Get the FRESH running balance after increment
            $newBalance = $stock->fresh()->quantity;

            // 2. Log Transaction (Now with valid running_balance)
            StockTransaction::create([
                'product_id' => $recall->product_id,
                'warehouse_id' => 1,
                'store_id' => $recall->store_id,
                'type' => 'recall_in',
                'quantity_change' => $request->received_quantity,
                'running_balance' => $newBalance, // <--- FIXED HERE (Was missing/null)
                'ware_user_id' => Auth::id(),
                'reference_id' => 'RECALL-' . $recall->id,
                'remarks' => $request->warehouse_remarks ?? 'Received from Store Recall'
            ]);

            // 3. Mark Request as Completed
            $recall->update([
                'received_quantity' => $request->received_quantity,
                'status' => RecallRequest::STATUS_COMPLETED,
                'received_by_ware_user_id' => Auth::id(),
                'warehouse_remarks' => $request->warehouse_remarks
            ]);
        });

        return back()->with('success', 'Stock Received Successfully.');
    }
}
