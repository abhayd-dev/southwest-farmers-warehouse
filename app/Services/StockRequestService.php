<?php

namespace App\Services;

use App\Models\StockRequest;
use App\Models\ProductBatch;
use App\Models\StockTransaction;
use App\Models\StoreStock; // <--- Critical Import
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
            $query->whereIn('status', [
                StockRequest::STATUS_PENDING, 
                StockRequest::STATUS_PARTIAL
            ]);
        }

        return $query->paginate(15);
    }

    /**
     * Approve & Dispatch Stock (Full Cycle: Warehouse Out -> Store In)
     */
    public function approveRequest($requestId, $dispatchQuantity, $note = null)
    {
        return DB::transaction(function () use ($requestId, $dispatchQuantity, $note) {
            $request = StockRequest::with('store')->findOrFail($requestId);
            $productId = $request->product_id;
            
            // 1. Validation
            if ($dispatchQuantity > $request->pending_quantity) {
                throw new \Exception("Cannot dispatch more than requested pending quantity ({$request->pending_quantity}).");
            }

            // 2. Fetch Warehouse Batches (FIFO: Oldest Expiry First)
            $batches = ProductBatch::where('product_id', $productId)
                        ->where('warehouse_id', 1) 
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
                        ->get();

            $totalAvailable = $batches->sum('quantity');
            if ($totalAvailable < $dispatchQuantity) {
                throw new \Exception("Insufficient Warehouse Stock. Available: {$totalAvailable}");
            }

            // 3. Deduct Stock from Warehouse (FIFO)
            $remainingToDeduct = $dispatchQuantity;

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $deductAmount = min($batch->quantity, $remainingToDeduct);

                // A. Update Warehouse Batch
                $batch->quantity -= $deductAmount;
                $batch->save();

                // B. Log Warehouse Transaction (OUT)
                StockTransaction::create([
                    'product_id' => $productId,
                    'warehouse_id' => 1,
                    'store_id' => null, // Warehouse side
                    'product_batch_id' => $batch->id,
                    'ware_user_id' => Auth::id(),
                    'type' => 'transfer_out',
                    'quantity_change' => -$deductAmount,
                    'running_balance' => $batch->quantity,
                    'reference_id' => 'REQ-' . $request->id,
                    'remarks' => "Dispatched to " . $request->store->store_name
                ]);

                $remainingToDeduct -= $deductAmount;
            }

            // 4. Add Stock to Store Inventory (Auto-Receive)
            $storeStock = StoreStock::firstOrCreate(
                ['store_id' => $request->store_id, 'product_id' => $productId],
                ['quantity' => 0, 'selling_price' => 0] // Default values
            );

            $storeStock->increment('quantity', $dispatchQuantity);

            // 5. Log Store Transaction (IN)
            StockTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => 1,
                'store_id' => $request->store_id, // Store side
                'product_batch_id' => null, // Stores don't track batches yet
                'ware_user_id' => Auth::id(),
                'type' => 'transfer_in',
                'quantity_change' => $dispatchQuantity,
                'running_balance' => $storeStock->quantity,
                'reference_id' => 'REQ-' . $request->id,
                'remarks' => "Received from Warehouse via Request #" . $request->id
            ]);

            // 6. Update Request Status
            $request->fulfilled_quantity += $dispatchQuantity;
            
            if ($request->fulfilled_quantity >= $request->requested_quantity) {
                $request->status = StockRequest::STATUS_COMPLETED;
            } else {
                $request->status = StockRequest::STATUS_PARTIAL;
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