<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\StoreStock;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecallController extends Controller
{
    public function index()
    {
        $requests = RecallRequest::with(['store', 'product'])->latest()->paginate(15);
        return view('warehouse.stock-control.recall.index', compact('requests'));
    }

    public function create()
    {
        return view('warehouse.stock-control.recall.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:store_details,id',
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|integer|min:1',
            'reason' => 'required|in:near_expiry,quality_issue,overstock,damage,other',
            'reason_remarks' => 'nullable|string',
        ]);

        RecallRequest::create([
            'store_id' => $request->store_id,
            'product_id' => $request->product_id,
            'requested_quantity' => $request->requested_quantity,
            'reason' => $request->reason,
            'reason_remarks' => $request->reason_remarks,
            'initiated_by' => Auth::id(),
            'status' => RecallRequest::STATUS_PENDING_STORE_APPROVAL,
        ]);

        return redirect()->route('warehouse.stock-control.recall.index')->with('success', 'Recall request created');
    }

    public function show(RecallRequest $recall)
    {
        $recall->load(['store', 'product']);
        return view('warehouse.stock-control.recall.show', compact('recall'));
    }

    public function approve(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'approved_quantity' => 'required|integer|min:1|lte:' . $recall->requested_quantity,
            'store_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $recall) {
            $recall->update([
                'approved_quantity' => $request->approved_quantity,
                'store_remarks' => $request->store_remarks,
                'status' => $recall->requested_quantity == $request->approved_quantity 
                    ? RecallRequest::STATUS_APPROVED_BY_STORE 
                    : RecallRequest::STATUS_PARTIAL_APPROVED,
            ]);
        });

        return back()->with('success', 'Recall approved');
    }

    public function reject(Request $request, RecallRequest $recall)
    {
        $request->validate(['store_remarks' => 'required|string']);

        $recall->update([
            'store_remarks' => $request->store_remarks,
            'status' => RecallRequest::STATUS_REJECTED_BY_STORE,
        ]);

        return back()->with('success', 'Recall rejected');
    }

    public function dispatch(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'batches' => 'required|array',
            'batches.*.batch_id' => 'required|exists:product_batches,id',
            'batches.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $recall) {
            $totalDispatched = 0;

            foreach ($request->batches as $batchData) {
                $batch = ProductBatch::find($batchData['batch_id']);
                $batch->decrement('remaining_quantity', $batchData['quantity']);
                $totalDispatched += $batchData['quantity'];

                StockTransaction::create([
                    'product_id' => $recall->product_id,
                    'product_batch_id' => $batch->id,
                    'store_id' => $recall->store_id,
                    'type' => 'recall_out',
                    'quantity_change' => -$batchData['quantity'],
                    'running_balance' => StoreStock::firstOrCreate([
                        'store_id' => $recall->store_id,
                        'product_id' => $recall->product_id,
                    ])->quantity,
                    'ware_user_id' => null,
                    'reference_id' => 'RECALL-' . $recall->id,
                    'remarks' => 'Recall dispatch',
                ]);
            }

            StoreStock::where('store_id', $recall->store_id)
                ->where('product_id', $recall->product_id)
                ->decrement('quantity', $totalDispatched);

            $recall->update([
                'dispatched_quantity' => $totalDispatched,
                'status' => RecallRequest::STATUS_DISPATCHED,
            ]);
        });

        return back()->with('success', 'Stock dispatched for recall');
    }

    public function receive(Request $request, RecallRequest $recall)
    {
        $request->validate([
            'received_quantity' => 'required|integer|min:1|lte:' . $recall->dispatched_quantity,
            'warehouse_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $recall) {
            ProductStock::where('warehouse_id', 1)
                ->where('product_id', $recall->product_id)
                ->increment('quantity', $request->received_quantity);

            StockTransaction::create([
                'product_id' => $recall->product_id,
                'store_id' => $recall->store_id,
                'type' => 'recall_in',
                'quantity_change' => $request->received_quantity,
                'running_balance' => ProductStock::where('product_id', $recall->product_id)->first()->quantity,
                'ware_user_id' => Auth::id(),
                'reference_id' => 'RECALL-' . $recall->id,
                'remarks' => 'Recall received',
            ]);

            $recall->update([
                'received_quantity' => $request->received_quantity,
                'received_by_ware_user_id' => Auth::id(),
                'warehouse_remarks' => $request->warehouse_remarks,
                'status' => $request->received_quantity == $recall->dispatched_quantity 
                    ? RecallRequest::STATUS_COMPLETED 
                    : RecallRequest::STATUS_RECEIVED,
            ]);
        });

        return back()->with('success', 'Recall stock received');
    }
}