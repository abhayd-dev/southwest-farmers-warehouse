<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockRequestService;
use App\Models\StockRequest;
use Illuminate\Http\Request;

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
            'admin_note' => 'required_if:status,rejected|nullable|string'
        ]);

        try {
            $this->service->processStatusChange($request->all());
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
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
            'remarks' => 'nullable|string',
            'purchase_ref' => 'nullable|string'
        ]);

        try {
            $this->service->addStockDirectly($request->all());
            return response()->json(['success' => true, 'message' => 'Stock added successfully to Warehouse']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
