<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filters: 'pending' (default), 'dispatched', 'history'
        $status = $request->get('status'); 
        $requests = $this->service->getRequests($status);
        
        return view('warehouse.stock-requests.index', compact('requests'));
    }

    /**
     * Display the specified resource (Dispatch Page).
     */
    public function show($id)
    {
        $stockRequest = StockRequest::with([
            'store', 
            'product.batches' => function($q) {
                // Show only active batches to help Admin decide
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            },
            'storeStock'
        ])->findOrFail($id);

        return view('warehouse.stock-requests.show', compact('stockRequest'));
    }

    /**
     * Update the specified resource (Approve/Reject).
     */
    public function update(Request $request, $id)
    {
        $action = $request->input('action'); // 'approve' or 'reject'

        try {
            if ($action === 'approve') {
                $request->validate([
                    'dispatch_quantity' => 'required|integer|min:1',
                ]);
                
                // Call Service to Deduct from Warehouse & Add to Store
                $this->service->approveRequest(
                    $id, 
                    $request->dispatch_quantity, 
                    $request->admin_note
                );
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Stock dispatched & added to Store inventory successfully.');

            } elseif ($action === 'reject') {
                $request->validate(['admin_note_reject' => 'required|string']);
                
                $this->service->rejectRequest($id, $request->admin_note_reject);
                
                return redirect()->route('warehouse.stock-requests.index')
                    ->with('success', 'Stock Request rejected.');
            }
        } catch (\Exception $e) {
            // Catch errors like "Insufficient Stock"
            return back()->withInput()->with('error', 'Action Failed: ' . $e->getMessage());
        }
    }
}