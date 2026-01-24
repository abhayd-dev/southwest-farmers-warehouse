<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\StoreStock;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\StoreDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class RecallController extends Controller
{
    // ===== MAIN TAB VIEW =====
    public function indexTabs()
    {
        return view('warehouse.stock-control.recall.index-tabs');
    }

    // ===== TAB 1: MY REQUESTS (Warehouse-Initiated) =====
    public function myRequests(Request $request)
    {
        $query = RecallRequest::where('initiated_by', Auth::id())
            ->with(['store', 'product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return DataTables::of($query)
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('status_badge', function ($row) {
                $colors = [
                    'pending_store_approval' => 'warning',
                    'approved_by_store' => 'info',
                    'partial_approved' => 'info',
                    'rejected_by_store' => 'danger',
                    'dispatched' => 'primary',
                    'received' => 'success',
                    'completed' => 'success'
                ];
                $color = $colors[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucwords(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stock-control.recall.show', $row) . '" 
                   class="btn btn-sm btn-outline-primary">
                   <i class="mdi mdi-eye"></i> View
                </a>
            ')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    // ===== TAB 2: STORE REQUESTS (Store → Warehouse) =====
    public function storeRequests(Request $request)
    {
        $query = RecallRequest::whereNotNull('approved_by_store_user_id')
            ->with(['store', 'product', 'storeApprover']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return DataTables::of($query)
            ->addColumn('store_name', fn($row) => $row->store->store_name ?? '-')
            ->addColumn('product_name', fn($row) => $row->product->product_name ?? '-')
            ->addColumn('status_badge', function ($row) {
                $colors = [
                    'pending_store_approval' => 'warning',
                    'approved_by_store' => 'info',
                    'partial_approved' => 'info',
                    'rejected_by_store' => 'danger',
                    'dispatched' => 'primary',
                    'received' => 'success',
                    'completed' => 'success'
                ];
                $color = $colors[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucwords(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stock-control.recall.show', $row) . '" 
                   class="btn btn-sm btn-outline-primary">
                   <i class="mdi mdi-eye"></i> View
                </a>
            ')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    // ===== TAB 3: EXPIRY & DAMAGE REPORTS =====
    public function expiryDamage(Request $request)
    {
        $query = ProductBatch::query()
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('store_details', 'product_batches.store_id', '=', 'store_details.id')
            ->select([
                'product_batches.*',
                'products.product_name',
                'products.sku',
                'product_categories.name as category_name',
                'store_details.store_name',
                DB::raw('(product_batches.expiry_date - CURRENT_DATE) as days_left'),
                DB::raw('(product_batches.quantity * product_batches.cost_price) as value')
            ])
            ->where(function ($q) {
                $q->where('product_batches.damaged_quantity', '>', 0)
                  ->orWhere('product_batches.expiry_date', '<=', now()->addDays(90));
            });

        if ($request->filled('date_from')) {
            $query->whereDate('product_batches.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('product_batches.created_at', '<=', $request->date_to);
        }

        if ($request->filled('store_id')) {
            $query->where('product_batches.store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('products.id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->filled('report_type')) {
            if ($request->report_type === 'expiry') {
                $query->where('product_batches.expiry_date', '<=', now()->addDays(90));
            } elseif ($request->report_type === 'damage') {
                $query->where('product_batches.damaged_quantity', '>', 0);
            }
        }

        return DataTables::of($query)
            ->addColumn('status', function ($row) {
                $daysLeft = $row->days_left;
                if ($daysLeft <= 0) {
                    return '<span class="badge bg-danger">Expired</span>';
                }
                if ($daysLeft <= 15) {
                    return '<span class="badge bg-danger">Critical (' . $daysLeft . ' days)</span>';
                }
                if ($daysLeft <= 30) {
                    return '<span class="badge bg-warning">Urgent (' . $daysLeft . ' days)</span>';
                }
                return '<span class="badge bg-info">Warning (' . $daysLeft . ' days)</span>';
            })
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stocks.history', $row->product_id) . '" 
                   class="btn btn-sm btn-outline-info">
                   <i class="mdi mdi-history"></i> History
                </a>
            ')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    // ===== CREATE RECALL =====
    public function create()
    {
        $stores = StoreDetail::where('is_active', true)->get();
        $products = Product::where('is_active', true)->whereNull('store_id')->get();
        return view('warehouse.stock-control.recall.create', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:store_details,id',
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|integer|min:1',
            'reason' => 'required|in:near_expiry,quality_issue,overstock,damage,other',
            'reason_remarks' => 'nullable|string',
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

        return redirect()->route('warehouse.stock-control.recall')->with('success', 'Recall request created');
    }

    // ===== SHOW RECALL DETAIL =====
    public function show(RecallRequest $recall)
    {
        $recall->load(['store', 'product', 'initiator', 'storeApprover', 'warehouseReceiver']);
        
        $batches = ProductBatch::where('product_id', $recall->product_id)
            ->where('warehouse_id', 1)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        return view('warehouse.stock-control.recall.show', compact('recall', 'batches'));
    }

    // ===== APPROVE/REJECT =====
    public function approve(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'approved_quantity' => 'required|integer|min:1|lte:' . $recall->requested_quantity,
            'store_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $recall) {
            $recall->update([
                'approved_quantity' => $request->approved_quantity,
                'store_remarks' => $request->store_remarks,
                'status' => $request->approved_quantity == $recall->requested_quantity 
                    ? RecallRequest::STATUS_APPROVED_BY_STORE 
                    : RecallRequest::STATUS_PARTIAL_APPROVED,
                'approved_by_store_user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Recall approved');
    }

    public function reject(Request $request, RecallRequest $recall)
    {
        $request->validate(['store_remarks' => 'required|string']);

        $recall->update([
            'store_remarks' => $request->store_remarks,
            'status' => RecallRequest::STATUS_REJECTED_BY_STORE,
            'approved_by_store_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Recall rejected');
    }

    // ===== DISPATCH =====
    public function dispatch(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'batches' => 'required|array|min:1',
            'batches.*.batch_id' => 'required|exists:product_batches,id',
            'batches.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $recall) {
            $totalDispatched = 0;

            foreach ($request->batches as $batchData) {
                $batch = ProductBatch::findOrFail($batchData['batch_id']);
                
                if ($batch->quantity < $batchData['quantity']) {
                    throw new \Exception('Insufficient batch quantity');
                }

                $batch->decrement('quantity', $batchData['quantity']);
                $totalDispatched += $batchData['quantity'];

                StockTransaction::create([
                    'product_id' => $recall->product_id,
                    'product_batch_id' => $batch->id,
                    'store_id' => $recall->store_id,
                    'warehouse_id' => 1,
                    'type' => 'recall_out',
                    'quantity_change' => -$batchData['quantity'],
                    'running_balance' => $batch->quantity,
                    'ware_user_id' => Auth::id(),
                    'reference_id' => 'RECALL-' . $recall->id,
                    'remarks' => 'Dispatch for recall request',
                ]);
            }

            ProductStock::where('warehouse_id', 1)
                ->where('product_id', $recall->product_id)
                ->decrement('quantity', $totalDispatched);

            $recall->update([
                'dispatched_quantity' => $totalDispatched,
                'status' => RecallRequest::STATUS_DISPATCHED,
            ]);
        });

        return back()->with('success', 'Stock dispatched for recall');
    }

    // ===== RECEIVE =====
    public function receive(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'received_quantity' => 'required|integer|min:1|lte:' . $recall->dispatched_quantity,
            'warehouse_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $recall) {
            ProductStock::where('warehouse_id', 1)
                ->where('product_id', $recall->product_id)
                ->increment('quantity', $request->received_quantity);

            StockTransaction::create([
                'product_id' => $recall->product_id,
                'store_id' => $recall->store_id,
                'warehouse_id' => 1,
                'type' => 'recall_in',
                'quantity_change' => $request->received_quantity,
                'running_balance' => ProductStock::where('warehouse_id', 1)
                    ->where('product_id', $recall->product_id)
                    ->first()
                    ->quantity,
                'ware_user_id' => Auth::id(),
                'reference_id' => 'RECALL-' . $recall->id,
                'remarks' => 'Recall received',
            ]);

            $recall->update([
                'received_quantity' => $request->received_quantity,
                'received_by_ware_user_id' => Auth::id(),
                'warehouse_remarks' => $request->warehouse_remarks,
                'status' => $request->received_quantity == $recall->dispatched_quantity 
                    ? RecallRequest::STATUS_COMPLETED 
                    : RecallRequest::STATUS_RECEIVED,
            ]);
        });

        return back()->with('success', 'Recall stock received');
    }
}