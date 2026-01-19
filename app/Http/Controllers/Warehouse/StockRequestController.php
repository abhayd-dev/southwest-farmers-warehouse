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
        
        // Data for Stock In (Purchase In) Modal
        $products = Product::whereNull('store_id')->where('is_active', true)->get();
        $categories = ProductCategory::whereNull('store_id')->where('is_active', true)->get();
        
        return view('warehouse.stock-requests.index', compact('requests', 'products', 'categories'));
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

        return view('warehouse.stock-requests.show', compact('stockRequest'));
    }

    // Standard Form Submit (Fallback)
    public function update(Request $request, $id)
    {
        $action = $request->input('action'); 

        try {
            if ($action === 'approve') {
                $request->validate([
                    'dispatch_quantity' => 'required|numeric|min:1',
                ]);
                
                $this->service->processStatusChange([
                    'request_id' => $id,
                    'status' => 'dispatched',
                    'dispatch_quantity' => $request->dispatch_quantity,
                    'admin_note' => $request->admin_note
                ]);
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Stock dispatched successfully.');

            } elseif ($action === 'reject') {
                // Note: If using standard form, ensure input name matches this validation
                $request->validate(['admin_note' => 'required|string']);
                
                $this->service->processStatusChange([
                    'request_id' => $id,
                    'status' => 'rejected',
                    'admin_note' => $request->admin_note
                ]);
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Request rejected.');
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // AJAX Endpoint for Dispatch/Reject
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

    // AJAX Endpoint for Payment Verification
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

    // AJAX Endpoint for Direct Stock Purchase
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