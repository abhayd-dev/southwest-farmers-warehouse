<?php

namespace App\Services;

use App\Models\StockRequest;
use App\Models\ProductBatch;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockRequestService
{
    /**
     * Get list of requests with filters
     */
    public function getRequests($status = null)
    {
        $query = StockRequest::with(['store', 'product', 'storeStock'])->latest();

        if ($status) {
            if ($status === 'history') {
                $query->whereIn('status', [StockRequest::STATUS_COMPLETED, StockRequest::STATUS_REJECTED]);
            } else {
                $query->where('status', $status);
            }
        } else {
            // Default: Show active requests
            $query->whereIn('status', [
                StockRequest::STATUS_PENDING, 
                StockRequest::STATUS_PARTIAL
            ]);
        }

        return $query->paginate(15);
    }

    /**
     * Approve & Dispatch Stock (FIFO Logic)
     */
    public function approveRequest($requestId, $dispatchQuantity, $note = null)
    {
        return DB::transaction(function () use ($requestId, $dispatchQuantity, $note) {
            $request = StockRequest::findOrFail($requestId);
            $productId = $request->product_id;
            
            // 1. Validation
            if ($dispatchQuantity > $request->pending_quantity) {
                throw new \Exception("Cannot dispatch more than requested pending quantity ({$request->pending_quantity}).");
            }

            // 2. Fetch Batches (FIFO: Oldest Expiry First)
            $batches = ProductBatch::where('product_id', $productId)
                        ->where('warehouse_id', 1) // Assuming Single Warehouse ID 1
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc') // First Expiring First Out
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
                        ->get();

            $totalAvailable = $batches->sum('quantity');
            if ($totalAvailable < $dispatchQuantity) {
                throw new \Exception("Insufficient Warehouse Stock. Available: {$totalAvailable}");
            }

            // 3. Deduct Stock from Batches
            $remainingToDeduct = $dispatchQuantity;

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $deductAmount = min($batch->quantity, $remainingToDeduct);

                // Update Batch
                $batch->quantity -= $deductAmount;
                $batch->save();

                // Log Transaction (Outward Transfer)
                StockTransaction::create([
                    'product_id' => $productId,
                    'warehouse_id' => 1,
                    'product_batch_id' => $batch->id,
                    'user_id' => Auth::id(),
                    'type' => 'transfer_out',
                    'quantity_change' => -$deductAmount,
                    'running_balance' => $batch->quantity, // Balance of this batch
                    'reference_id' => 'REQ-' . $request->id,
                    'remarks' => "Dispatch to " . $request->store->store_name
                ]);

                $remainingToDeduct -= $deductAmount;
            }

            // 4. Update Request Status
            $request->fulfilled_quantity += $dispatchQuantity;
            
            if ($request->fulfilled_quantity >= $request->requested_quantity) {
                $request->status = StockRequest::STATUS_DISPATCHED; // Fully Sent (In Transit)
            } else {
                $request->status = StockRequest::STATUS_PARTIAL; // Partially Sent
            }

            if ($note) $request->admin_note = $note;
            $request->save();

            return $request;
        });
    }

    /**
     * Reject Request
     */
    public function rejectRequest($requestId, $note)
    {
        $request = StockRequest::findOrFail($requestId);
        $request->update([
            'status' => StockRequest::STATUS_REJECTED,
            'admin_note' => $note
        ]);
        return $request;
    }
}