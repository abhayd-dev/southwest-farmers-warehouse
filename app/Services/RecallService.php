<?php

namespace App\Services;

use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecallService
{
    // Create Warehouse-Initiated Request
    public function createRecall(array $data)
    {
        return DB::transaction(function () use ($data) {
            return RecallRequest::create([
                'store_id' => $data['store_id'],
                'product_id' => $data['product_id'],
                'requested_quantity' => $data['requested_quantity'],
                'reason' => $data['reason'],
                'reason_remarks' => $data['reason_remarks'] ?? null,
                'initiated_by' => Auth::id(),
                'status' => RecallRequest::STATUS_PENDING_STORE_APPROVAL, // Warehouse asks Store
            ]);
        });
    }

    // Approve a Store-Initiated Request
    public function approveStoreRequest(RecallRequest $recall, int $approvedQty, ?string $remarks)
    {
        return DB::transaction(function () use ($recall, $approvedQty, $remarks) {
            $recall->update([
                'approved_quantity' => $approvedQty,
                'warehouse_remarks' => $remarks,
                'status' => RecallRequest::STATUS_APPROVED, // Only 'approved' as per requirement
            ]);
        });
    }

    // Receive Stock into Warehouse (Final Step)
    public function processReceive(RecallRequest $recall, int $quantity, string $remarks = null)
    {
        DB::transaction(function () use ($recall, $quantity, $remarks) {
            
            // 1. Add Stock to Warehouse Global Stock
            ProductStock::updateOrCreate(
                ['warehouse_id' => 1, 'product_id' => $recall->product_id],
                ['quantity' => DB::raw("quantity + $quantity")]
            );

            // Note: Ideally, we should also increment a specific Warehouse Batch here.
            // If the Store sent specific batch info (via dispatched batch logic), 
            // we would map it back. For now, we update global stock.

            // 2. Log Transaction
            StockTransaction::create([
                'product_id' => $recall->product_id,
                'store_id' => $recall->store_id,
                'warehouse_id' => 1,
                'type' => 'recall_in', // Stock In from Recall
                'quantity_change' => $quantity,
                'running_balance' => ProductStock::where('warehouse_id', 1)->where('product_id', $recall->product_id)->first()->quantity,
                'ware_user_id' => Auth::id(),
                'reference_id' => 'RECALL-' . $recall->id,
                'remarks' => $remarks ?? 'Received from Store Recall',
            ]);

            // 3. Update Status to Completed
            $recall->update([
                'received_quantity' => $quantity,
                'received_by_ware_user_id' => Auth::id(),
                'warehouse_remarks' => $remarks,
                'status' => RecallRequest::STATUS_COMPLETED,
            ]);
        });
    }
}