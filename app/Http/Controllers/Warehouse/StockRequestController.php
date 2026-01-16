<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Services\StockRequestService;
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
        $status = $request->get('status');
        $requests = $this->service->getRequests($status);
        
        return view('warehouse.stock-requests.index', compact('requests'));
    }

    public function show($id)
    {
        // Find request or fail
        $stockRequest = \App\Models\StockRequest::with(['store', 'product.batches' => function($q) {
            // Show available warehouse batches for this product to help admin decide
            $q->where('quantity', '>', 0)->orderBy('expiry_date');
        }])->findOrFail($id);

        return view('warehouse.stock-requests.show', compact('stockRequest'));
    }

    public function update(Request $request, $id)
    {
        $action = $request->input('action'); // 'approve' or 'reject'

        try {
            if ($action === 'approve') {
                $request->validate([
                    'dispatch_quantity' => 'required|integer|min:1',
                ]);
                
                $this->service->approveRequest(
                    $id, 
                    $request->dispatch_quantity, 
                    $request->admin_note
                );
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Stock dispatched successfully.');

            } elseif ($action === 'reject') {
                $request->validate(['admin_note' => 'required|string']);
                
                $this->service->rejectRequest($id, $request->admin_note);
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Request rejected.');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}