<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StockAudit;
use App\Models\StockAuditItem;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class StockAuditController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager load department
            $query = StockAudit::whereNotNull('warehouse_id')
                ->with(['initiator', 'department']) 
                ->latest();

            return DataTables::of($query)
                ->addColumn('audit_no', fn($row) => $row->audit_number)
                ->addColumn('type_label', function($row) {
                    // Show "Department: Frozen" or just "Full"
                    if($row->type === 'department' && $row->department) {
                        return '<span class="text-primary fw-bold">Dept: ' . $row->department->name . '</span>';
                    }
                    return '<span class="text-dark">Full Inventory</span>';
                })
                ->addColumn('date', fn($row) => $row->created_at->format('d M Y'))
                ->addColumn('status_badge', function($row) {
                    $colors = ['draft' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'];
                    return '<span class="badge bg-'.($colors[$row->status]??'primary').'">'.ucfirst(str_replace('_',' ',$row->status)).'</span>';
                })
                ->addColumn('action', function($row) {
                    return '<div class="action-btns">
                                <a href="'.route('warehouse.stock-control.audit.show', $row->id).'" class="btn btn-sm btn-outline-info btn-view" title="View / Process">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['type_label', 'status_badge', 'action']) 
                ->make(true);
        }
        return view('warehouse.stock-control.audit.index');
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return view('warehouse.stock-control.audit.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,department',
            'department_id' => 'required_if:type,department|nullable|exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            $audit = StockAudit::create([
                'audit_number' => 'AUD-WH-' . time(),
                'warehouse_id' => 1,
                'type' => $request->type,
                'department_id' => $request->department_id, // SAVE DEPARTMENT ID
                'status' => 'in_progress',
                'initiated_by' => Auth::id(),
                'notes' => $request->notes
            ]);

            // Query base
            $stockQuery = ProductStock::where('warehouse_id', 1)->with('product');

            // Apply Department Filter
            if ($request->type === 'department') {
                $stockQuery->whereHas('product', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            // ORDER BY BIN LOCATION for optimized walking path
            $stocks = $stockQuery->orderBy('bin_location')->get();

            if ($stocks->isEmpty()) {
                throw new \Exception("No products found for this audit criteria.");
            }

            foreach ($stocks as $stock) {
                StockAuditItem::create([
                    'stock_audit_id' => $audit->id,
                    'product_id' => $stock->product_id,
                    'system_qty' => $stock->quantity,
                    'physical_qty' => 0, 
                    'cost_price' => $stock->product->cost_price ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('warehouse.stock-control.audit.show', $audit->id)
                ->with('success', 'Audit Initiated! Snapshot taken.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ... (Show, UpdateCounts, Finalize methods remain unchanged) ...
    public function show($id) {
        // Eager load product stock to get bin_location
        $audit = StockAudit::with(['items.product.stock', 'initiator'])->findOrFail($id);
        return view('warehouse.stock-control.audit.show', compact('audit'));
    }

    public function updateCounts(Request $request, $id) {
        $audit = StockAudit::findOrFail($id);
        if($audit->status == 'completed') abort(403);
        foreach ($request->items as $itemId => $qty) {
            $item = StockAuditItem::where('stock_audit_id', $id)->where('id', $itemId)->first();
            if($item) {
                $item->physical_qty = $qty;
                $item->variance_qty = $qty - $item->system_qty;
                $item->save();
            }
        }
        return back()->with('success', 'Counts saved.');
    }

    public function finalize($id) {
        $audit = StockAudit::with('items')->findOrFail($id);
        if($audit->status == 'completed') abort(403);
        try {
            DB::beginTransaction();
            foreach ($audit->items as $item) {
                if ($item->variance_qty != 0) {
                    ProductStock::where('warehouse_id', 1)->where('product_id', $item->product_id)->increment('quantity', $item->variance_qty);
                    StockTransaction::create([
                        'product_id' => $item->product_id, 'warehouse_id' => 1, 'type' => 'adjustment',
                        'quantity_change' => $item->variance_qty, 'running_balance' => $item->system_qty + $item->variance_qty,
                        'ware_user_id' => Auth::id(), 'remarks' => "Cycle Count: {$audit->audit_number}"
                    ]);
                }
            }
            $audit->update(['status' => 'completed', 'completed_at' => now()]);
            DB::commit();
            return redirect()->route('warehouse.stock-control.audit.index')->with('success', 'Audit Finalized.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}