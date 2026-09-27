<?php

namespace App\Http\Controllers\Warehouse\Fulfillment;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Services\StockRequestService;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


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

        $query = StockRequest::with(['store', 'product', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('store', fn($q) => $q->where('store_name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn($q) => $q->where('product_name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhereHas('items.product', fn($q) => $q->where('product_name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($status === 'history') {
            $query->whereIn('status', [StockRequest::STATUS_COMPLETED, StockRequest::STATUS_REJECTED]);
        } elseif ($status === 'in_transit') {
            $query->where('status', StockRequest::STATUS_DISPATCHED);
        } else {
            $query->whereIn('status', [StockRequest::STATUS_PENDING, 'awaiting_approval']);
        }

        $requests = $query->latest()->paginate(15)->appends($request->query());

        // Stats counts
        $pendingCount = StockRequest::whereIn('status', ['pending', 'awaiting_approval'])->count();
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
            'items.product.batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            },
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

            $req = StockRequest::find($request->request_id);
            $action = $request->status == 'dispatched' ? 'Dispatched' : 'Rejected';
            $type = $request->status == 'dispatched' ? 'success' : 'danger';
            
            NotificationService::sendToAdmins(
                "Request {$action}", 
                "Stock Request #{$req->id} for {$req->store->store_name} was {$action} by " . auth()->user()->name, 
                $type, 
                route('warehouse.stock-requests.show', $req->id)
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock Dispatched Successfully (FIFO Applied)',
                'redirect' => route('warehouse.stock-requests.show', $request->request_id)
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stock status change failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.')], 400);
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

            NotificationService::sendToAdmins(
                "Payment Verified", 
                "Payment for Request #{$request->request_id} verified by " . auth()->user()->name, 
                'success',
                route('warehouse.stock-requests.show', $request->request_id)
            );
            return response()->json(['success' => true, 'message' => 'Payment verified & Stock Completed']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Verify payment failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.')], 400);
        }
    }

    public function purchaseIn(Request $request)
    {
        // Batch number and cost are optional: the Purchase In popup never had
        // those fields, so requiring them rejected every submission.
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'batch_number' => 'nullable|string|max:50',
            'mfg_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:mfg_date',
            'cost_price' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'purchase_ref' => 'nullable|string|max:100',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = (float) $request->quantity;

            DB::transaction(function () use ($request, $product, $quantity) {
                // Same path as Stock In elsewhere: batch + warehouse total (created
                // if the product has none yet) + a stock movement line.
                $batch = $product->addStock(1, $quantity, 'purchase_in', [
                    'batch_number' => $request->filled('batch_number') ? trim($request->batch_number) : 'PUR-' . now()->format('ymd-His'),
                    'mfg_date' => $request->mfg_date,
                    'exp_date' => $request->expiry_date,
                    'cost_price' => $request->filled('cost_price') ? $request->cost_price : ($product->cost_price ?? 0),
                ], Auth::id(), $request->remarks ?: 'Purchase received');

                StockTransaction::where('product_batch_id', $batch->id)->update([
                    'reference_id' => $request->filled('purchase_ref') ? trim($request->purchase_ref) : 'PUR-' . $batch->id,
                ]);
            });

            NotificationService::sendToAdmins(
                "Direct Stock Added",
                "Added {$quantity} units of {$product->product_name}",
                'info',
                route('warehouse.stocks.index')
            );

            return response()->json(['success' => true, 'message' => "Added {$quantity} units of {$product->product_name} to warehouse stock."]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Purchase in failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => \App\Support\ErrorMessage::from($e, 'Something went wrong. Please try again later.')], 400);
        }
    }

    public function approveStorePo(Request $request, $id)
    {
        $stockRequest = StockRequest::findOrFail($id);
        $stockRequest->update([
            'status' => StockRequest::STATUS_PENDING,
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        NotificationService::sendToAdmins(
            "Store PO Approved",
            "Store PO #{$stockRequest->id} for {$stockRequest->store->store_name} was approved by " . auth()->user()->name,
            'success',
            route('warehouse.stock-requests.show', $stockRequest->id)
        );

        return response()->json(['success' => true, 'message' => 'Store PO Approved successfully.']);
    }

    public function printPickerSheet($id)
    {
        $stockRequest = StockRequest::with(['store', 'items.product', 'product'])->findOrFail($id);
        return view('warehouse.stock-requests.print-picker', compact('stockRequest'));
    }
}
