<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Services\StockRequestService;
use App\Models\StockRequest;
use App\Models\Product;
use App\Models\ProductCategory;
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
        $requests = $this->service->getRequests($status);
        
        $products = Product::whereNull('store_id')->where('is_active', true)->get();
        
        return view('warehouse.stock-requests.index', compact('requests', 'products'));
    }

    public function show($id)
    {
        $stockRequest = StockRequest::with([
            'store', 
            'product.batches' => function($q) {
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