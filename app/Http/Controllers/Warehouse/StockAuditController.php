<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StockAudit;
use App\Models\StockAuditItem;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class StockAuditController extends Controller
{
    // 1. List Audits
    public function index(Request $request)
    {
        set_time_limit(300);
        if ($request->ajax()) {
            $query = StockAudit::whereNotNull('warehouse_id')->with('initiator')->latest();
            return DataTables::of($query)
                ->addColumn('audit_no', fn($row) => $row->audit_number)
                ->addColumn('date', fn($row) => $row->created_at->format('d M Y'))
                ->addColumn('status_badge', function($row) {
                    $colors = ['draft' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'];
                    return '<span class="badge bg-'.($colors[$row->status]??'primary').'">'.ucfirst(str_replace('_',' ',$row->status)).'</span>';
                })
                ->addColumn('action', function($row) {
                    return '<a href="'.route('warehouse.stock-control.audit.show', $row->id).'" class="btn btn-sm btn-outline-primary">Open</a>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('warehouse.stock-control.audit.index');
    }

    // 2. Create Audit (Setup Page)
    public function create()
    {
        return view('warehouse.stock-control.audit.create');
    }

    // 3. Store Audit (Freeze Inventory Snapshot)
    public function store(Request $request)
    {
        set_time_limit(300);
        // Start Audit Logic
        try {
            DB::beginTransaction();

            $audit = StockAudit::create([
                'audit_number' => 'AUD-WH-' . time(),
                'warehouse_id' => 1, // Central Warehouse
                'type' => $request->type ?? 'full',
                'status' => 'in_progress',
                'initiated_by' => Auth::id(),
                'notes' => $request->notes
            ]);

            // Snapshot Current Stock
            // Agar "Full Audit" hai toh saare products le lo
            $stocks = ProductStock::where('warehouse_id', 1)->with('product')->get();

            foreach ($stocks as $stock) {
                StockAuditItem::create([
                    'stock_audit_id' => $audit->id,
                    'product_id' => $stock->product_id,
                    'system_qty' => $stock->quantity, // Current System Stock
                    'physical_qty' => 0, // Default 0, user will update
                    'cost_price' => $stock->product->cost_price ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('warehouse.stock-control.audit.show', $audit->id)
                ->with('success', 'Audit Initiated! Inventory snapshot taken.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 4. Show Audit (Enter Counts)
    public function show($id)
    {
        set_time_limit(300);
        $audit = StockAudit::with(['items.product'])->findOrFail($id);
        return view('warehouse.stock-control.audit.show', compact('audit'));
    }

    // 5. Update Counts (Save Progress)
    public function updateCounts(Request $request, $id)
    {
        set_time_limit(300);
        $audit = StockAudit::findOrFail($id);
        if($audit->status == 'completed') abort(403, 'Audit already finalized');

        // Loop through items and update physical qty
        foreach ($request->items as $itemId => $qty) {
            $item = StockAuditItem::where('stock_audit_id', $id)->where('id', $itemId)->first();
            if($item) {
                $item->physical_qty = $qty;
                $item->variance_qty = $qty - $item->system_qty;
                $item->save();
            }
        }

        return back()->with('success', 'Counts saved successfully.');
    }

    // 6. Finalize Audit (Adjust Stock)
    public function finalize($id)
    {
        set_time_limit(300);
        $audit = StockAudit::with('items')->findOrFail($id);
        if($audit->status == 'completed') abort(403);

        try {
            DB::beginTransaction();

            foreach ($audit->items as $item) {
                if ($item->variance_qty != 0) {
                    // Adjust Product Stock
                    // Note: Variance -ve means missing stock, +ve means extra stock
                    $action = $item->variance_qty > 0 ? 'add' : 'subtract';
                    
                    // Update Stock Table
                    ProductStock::where('warehouse_id', 1)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', $item->variance_qty); // Direct increment handles +/-

                    // Log Transaction
                    StockTransaction::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => 1,
                        'type' => 'adjustment',
                        'quantity_change' => $item->variance_qty,
                        'running_balance' => $item->system_qty + $item->variance_qty,
                        'ware_user_id' => Auth::id(),
                        'remarks' => "Cycle Count Variance: {$audit->audit_number}"
                    ]);
                }
            }

            $audit->update(['status' => 'completed', 'completed_at' => now()]);

            DB::commit();
            return redirect()->route('warehouse.stock-control.audit.index')->with('success', 'Audit Finalized & Inventory Adjusted.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}