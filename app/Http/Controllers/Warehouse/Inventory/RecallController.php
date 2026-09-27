<?php

namespace App\Http\Controllers\Warehouse\Inventory;

use App\Http\Controllers\Controller;
use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\StoreDetail;
use App\Models\Product;
use App\Services\NotificationService;
use App\Support\DisplayDay;
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
        $this->filterByDay($query, $request, 'recall_requests.created_at');

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
        $this->filterByDay($query, $request, 'recall_requests.created_at');

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
                'products.upc',
                DB::raw('(product_batches.expiry_date - CURRENT_DATE) as days_left')
            ])
            ->leftJoin('store_details', 'product_batches.store_id', '=', 'store_details.id')
            ->addSelect(DB::raw("COALESCE(store_details.store_name, 'Warehouse') as store_name"));

        // No store chosen: the warehouse's own batches (as before); a store: that store's.
        if ($request->filled('store_id')) {
            $query->where('product_batches.store_id', $request->store_id);
        } else {
            $query->where('product_batches.warehouse_id', 1)->whereNull('product_batches.store_id');
        }

        // Report Type: expiring (within 90 days / expired), damaged, or both.
        $expiring = fn ($q) => $q->whereNotNull('product_batches.expiry_date')->where('product_batches.expiry_date', '<=', now()->addDays(90)->toDateString());
        $damaged = fn ($q) => $q->where('product_batches.damaged_quantity', '>', 0);
        match ($request->input('report_type')) {
            'expiry' => $query->where($expiring),
            'damage' => $query->where($damaged),
            default => $query->where(fn ($q) => $q->where($damaged)->orWhere($expiring)),
        };

        // Date From / To = the expiry date range (a plain date, no time zone).
        if ($from = $this->filterDate($request->input('date_from'))) {
            $query->where('product_batches.expiry_date', '>=', $from);
        }
        if ($to = $this->filterDate($request->input('date_to'))) {
            $query->where('product_batches.expiry_date', '<=', $to);
        }

        return DataTables::of($query)
            // Computed columns: tell the search box what they are (an alias can't go in WHERE).
            ->filterColumn('store_name', fn ($q, $keyword) => $q->whereRaw("COALESCE(store_details.store_name, 'Warehouse') ILIKE ?", ['%' . $keyword . '%']))
            ->filterColumn('days_left', fn ($q, $keyword) => $q->whereRaw('(product_batches.expiry_date - CURRENT_DATE)::text = ?', [trim($keyword)]))
            ->filterColumn('product_name', fn ($q, $keyword) => $q->where('products.product_name', 'ILIKE', '%' . $keyword . '%'))
            ->addColumn('status', fn($row) => $row->days_left <= 0 ? '<span class="badge bg-danger">Expired</span>' : '<span class="badge bg-warning">Warning</span>')
            ->rawColumns(['status'])
            ->make(true);
    }

    /** Date From / To as the day staff mean (Chicago), not the UTC date. */
    private function filterByDay($query, Request $request, string $column): void
    {
        if ($from = $this->filterDate($request->input('date_from'))) {
            $query->where($column, '>=', DisplayDay::start($from));
        }
        if ($to = $this->filterDate($request->input('date_to'))) {
            $query->where($column, '<=', DisplayDay::end($to));
        }
    }

    /** Y-m-d from the date picker (or 09/26/2026); null when empty or not a date. */
    private function filterDate(?string $value): ?string
    {
        $value = trim((string) $value);
        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
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

        $recall = RecallRequest::create([
            'store_id' => $request->store_id,
            'product_id' => $request->product_id,
            'requested_quantity' => $request->requested_quantity,
            'reason' => $request->reason,
            'reason_remarks' => $request->reason_remarks,
            'initiated_by' => Auth::id(),
            'status' => RecallRequest::STATUS_PENDING_STORE_APPROVAL,
        ]);

        NotificationService::sendToAdmins(
            'Recall Initiated',
            "New recall #{$recall->id} started for {$recall->store->store_name}",
            'info',
            route('warehouse.stock-control.recall.show', $recall->id)
        );


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

        NotificationService::sendToAdmins(
            'Recall Approved',
            "Recall request #{$recall->id} approved by " . auth()->user()->name,
            'success',
            route('warehouse.stock-control.recall.show', $recall->id)
        );

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

        NotificationService::sendToAdmins(
            'Recall Rejected',
            "Recall request #{$recall->id} rejected by " . auth()->user()->name,
            'danger',
            route('warehouse.stock-control.recall.show', $recall->id)
        );

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
