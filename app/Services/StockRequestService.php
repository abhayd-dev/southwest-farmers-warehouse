<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockRequest;
use App\Models\ProductBatch;
use App\Models\StockTransaction;
use App\Models\StoreStock;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StockRequestService
{
    public function getRequests($status)
    {
        $query = StockRequest::with(['store', 'product'])->latest();

        if ($status === 'history') {
            $query->whereIn('status', [StockRequest::STATUS_COMPLETED, StockRequest::STATUS_REJECTED]);
        } elseif ($status === 'in_transit') {
            $query->whereIn('status', [StockRequest::STATUS_DISPATCHED]);
        } else {
            $query->where('status', $status);
        }

        return $query->paginate(15);
    }

    public function processStatusChange($data)
    {
        return DB::transaction(function () use ($data) {
            $request = StockRequest::findOrFail($data['request_id']);

            $currentFulfilled = $request->fulfilled_quantity ?? 0;
            $pendingQuantity = $request->requested_quantity - $currentFulfilled;

            if ($data['status'] === StockRequest::STATUS_REJECTED) {
                if ($currentFulfilled > 0 || $request->status === StockRequest::STATUS_DISPATCHED) {
                    throw new \Exception('Cannot reject a request after stock has been dispatched.');
                }

                $request->update([
                    'status' => StockRequest::STATUS_REJECTED,
                    'admin_note' => $data['admin_note'] ?? null
                ]);
                return;
            }

            if ($data['status'] === StockRequest::STATUS_DISPATCHED) {
                if ($pendingQuantity <= 0) {
                    throw new \Exception('No pending quantity left to dispatch.');
                }

                $dispatchQty = $data['dispatch_quantity'];

                if ($dispatchQty > $pendingQuantity) {
                    throw new \Exception("Dispatch quantity cannot exceed pending quantity ({$pendingQuantity}).");
                }

                $productId = $request->product_id;

                $this->dispatchToStore($request, $dispatchQty);

                $request->update([
                    'status' => StockRequest::STATUS_DISPATCHED,
                    'fulfilled_quantity' => $currentFulfilled + $dispatchQty,
                    'admin_note' => $data['admin_note'] ?? null
                ]);
            }
        });
    }

    public function dispatchToStore(StockRequest $request, $dispatchQty)
    {
        $productId = $request->product_id;
        $warehouseId = 1; // Central Warehouse

        // 1. FIFO LOGIC: Get Oldest Batches First
        // We use lockForUpdate to prevent two admins dispatching the same batch at the exact same time
        $batches = ProductBatch::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc') // Oldest Expiry First
            ->lockForUpdate()
            ->get();

        $totalAvailable = $batches->sum('quantity');

        if ($totalAvailable < $dispatchQty) {
            throw new \Exception("Insufficient warehouse stock. Available: {$totalAvailable}, Requested: {$dispatchQty}");
        }

        $remainingToDispatch = $dispatchQty;
        $dispatchedBatchLog = []; // To store in JSON

        foreach ($batches as $batch) {
            if ($remainingToDispatch <= 0) break;

            // How much can we take from this specific batch?
            $qtyFromThisBatch = min($batch->quantity, $remainingToDispatch);

            // A. Deduct from Warehouse Batch
            $batch->decrement('quantity', $qtyFromThisBatch);

            // B. Log the Transaction (Warehouse Out)
            StockTransaction::create([
                'product_id' => $productId,
                'product_batch_id' => $batch->id,
                'warehouse_id' => $warehouseId,
                'store_id' => $request->store_id, // Target Store
                'type' => 'transfer_out',
                'quantity_change' => -$qtyFromThisBatch,
                'running_balance' => $batch->quantity, // Balance of this batch
                'ware_user_id' => Auth::id(),
                'reference_id' => 'REQ-' . $request->id,
                'remarks' => "FIFO Dispatch to {$request->store->store_name}"
            ]);

            // C. Add to Log for Challan/JSON
            $dispatchedBatchLog[] = [
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date, // Keep as string or format it
                'qty' => $qtyFromThisBatch,
                'cost_price' => $batch->cost_price
            ];

            $remainingToDispatch -= $qtyFromThisBatch;
        }

        // 2. Deduct from Warehouse Total Stock (ProductStock Table)
        ProductStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->decrement('quantity', $dispatchQty);

        $existingDetails = $request->batch_details ?? [];
        $updatedDetails = array_merge($existingDetails, $dispatchedBatchLog);

        $request->batch_details = $updatedDetails;
        $request->save();
    }

    public function verifyPayment($requestData)
    {
        return DB::transaction(function () use ($requestData) {
            $request = StockRequest::findOrFail($requestData->request_id);

            if ($request->status !== StockRequest::STATUS_DISPATCHED) {
                throw new \Exception("Invalid status for verification. Request must be Dispatched first.");
            }

            $path = $requestData->file('warehouse_payment_proof')->store('payment_proofs', 'public');

            $request->update([
                'status' => StockRequest::STATUS_COMPLETED,
                'warehouse_payment_proof' => $path,
                'warehouse_remarks' => $requestData->warehouse_remarks,
                'verified_at' => Carbon::now()
            ]);

            $storeStock = StoreStock::firstOrCreate(
                ['store_id' => $request->store_id, 'product_id' => $request->product_id],
                ['quantity' => 0, 'selling_price' => 0]
            );

            $storeStock->increment('quantity', $request->fulfilled_quantity);

            StockTransaction::create([
                'product_id' => $request->product_id,
                'warehouse_id' => 1,
                'store_id' => $request->store_id,
                'product_batch_id' => null,
                'ware_user_id' => Auth::id(),
                'type' => 'transfer_in',
                'quantity_change' => $request->fulfilled_quantity,
                'running_balance' => $storeStock->quantity,
                'reference_id' => 'REQ-' . $request->id,
                'remarks' => "Stock Received & Payment Verified"
            ]);
        });
    }

    public function addStockDirectly($data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $batch = ProductBatch::create([
                'product_id' => $product->id,
                'warehouse_id' => 1,
                'batch_number' => 'PUR-' . time(),
                'cost_price' => $product->cost_price,
                'quantity' => $data['quantity'],
            ]);

            $stock = ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => 1],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $data['quantity']);

            StockTransaction::create([
                'product_id' => $product->id,
                'warehouse_id' => 1,
                'store_id' => null,
                'product_batch_id' => $batch->id,
                'ware_user_id' => Auth::id(),
                'type' => 'purchase_in',
                'quantity_change' => $data['quantity'],
                'running_balance' => $stock->quantity,
                'reference_id' => $data['purchase_ref'] ?? 'DIRECT-ADD',
                'remarks' => $data['remarks'] ?? 'Direct Purchase In'
            ]);
        });
    }
}
