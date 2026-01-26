<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Services\StockRequestService;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StockRequestController extends Controller
{
    protected $service;

    public function __construct(StockRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->input('search');

        $query = StockRequest::with(['store', 'product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('store', fn($q) => $q->where('store_name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn($q) => $q->where('product_name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($status === 'history') {
            $query->whereIn('status', [StockRequest::STATUS_COMPLETED, StockRequest::STATUS_REJECTED]);
        } elseif ($status === 'in_transit') {
            $query->where('status', StockRequest::STATUS_DISPATCHED);
        } else {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15)->appends($request->query());

        // Stats counts
        $pendingCount = StockRequest::where('status', 'pending')->count();
        $inTransitCount = StockRequest::where('status', 'dispatched')->count();
        $completedCount = StockRequest::where('status', 'completed')->count();
        $rejectedCount = StockRequest::where('status', 'rejected')->count();

        return view('warehouse.stock-requests.index', compact(
            'requests',
            'pendingCount',
            'inTransitCount',
            'completedCount',
            'rejectedCount'
        ));
    }
    public function show($id)
    {
        $stockRequest = StockRequest::with([
            'store',
            'product.batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            },
            'storeStock'
        ])->findOrFail($id);

        $products = Product::whereNull('store_id')->where('is_active', true)->get();

        return view('warehouse.stock-requests.show', compact('stockRequest', 'products'));
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:stock_requests,id',
            'status' => 'required|in:dispatched,rejected',
            'dispatch_quantity' => 'required_if:status,dispatched|nullable|numeric|min:1',
            'admin_note' => 'nullable|string'
        ]);

        try {
            $this->service->processStatusChange($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Stock Dispatched Successfully (FIFO Applied)',
                'redirect' => route('warehouse.stock-requests.show', $request->request_id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:stock_requests,id',
            'warehouse_payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'warehouse_remarks' => 'required|string'
        ]);

        try {
            $this->service->verifyPayment($request);
            return response()->json(['success' => true, 'message' => 'Payment verified & Stock Completed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function purchaseIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'batch_number' => 'required|string|max:50',
            'mfg_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:mfg_date',
            'cost_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'purchase_ref' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create Batch
                $batch = ProductBatch::create([
                    'product_id' => $request->product_id,
                    'warehouse_id' => 1,
                    'store_id' => null,
                    'batch_number' => $request->batch_number,
                    'manufacturing_date' => $request->mfg_date,
                    'expiry_date' => $request->expiry_date,
                    'cost_price' => $request->cost_price,
                    'quantity' => $request->quantity,
                    'is_active' => true,
                ]);

                // 2. Update Warehouse Stock
                ProductStock::updateOrCreate(
                    ['warehouse_id' => 1, 'product_id' => $request->product_id],
                    ['quantity' => DB::raw('quantity + ' . $request->quantity)]
                );

                // 3. Log Transaction
                StockTransaction::create([
                    'product_id' => $request->product_id,
                    'product_batch_id' => $batch->id,
                    'warehouse_id' => 1,
                    'type' => 'purchase_in',
                    'quantity_change' => $request->quantity,
                    'running_balance' => ProductStock::where('warehouse_id', 1)->where('product_id', $request->product_id)->first()->quantity,
                    'ware_user_id' => Auth::id(),
                    'reference_id' => 'PUR-' . $request->purchase_ref ?? time(),
                    'remarks' => $request->remarks ?? 'Purchase received',
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Batch created & stock added to warehouse']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
