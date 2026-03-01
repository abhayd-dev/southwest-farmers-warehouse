<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use App\Models\WareUser;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductStockController extends Controller
{
    /**
     * Display Current Stock Level (Snapshot)
     */
    public function index(Request $request)
    {
        // Fetch products with their total stock quantity
        $stocks = ProductStock::with(['product', 'product.category', 'product.subcategory'])
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->whereHas('product', function ($p) use ($s) {
                    $p->where('product_name', 'ilike', "%$s%")
                        ->orWhere('sku', 'ilike', "%$s%")
                        ->orWhere('barcode', 'ilike', "%$s%");
                });
            })
            ->when($request->category, function ($q) use ($request) {
                $q->whereHas('product', fn($p) => $p->where('category_id', $request->category));
            })
            // Filter by low stock
            ->when($request->filter == 'low_stock', function ($q) {
                $q->whereColumn('quantity', '<=', 'min_stock_level');
            })
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->orWhere('bin_location', 'ilike', "%$s%");
            })
            ->latest('updated_at')
            ->paginate(15);

        // Calculate Transit Stock for the displayed items
        $productIds = $stocks->pluck('product_id')->toArray();
        $transitStocks = \App\Models\StorePurchaseOrderItem::whereIn('product_id', $productIds)
            ->whereHas('storePurchaseOrder', function($q) {
                $q->whereIn('status', ['approved', 'dispatched']);
            })
            ->selectRaw('product_id, SUM(pending_qty) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $stocks->getCollection()->transform(function($stock) use ($transitStocks) {
            $stock->in_transit_qty = $transitStocks[$stock->product_id] ?? 0;
            return $stock;
        });

        $categories = ProductCategory::active()->get();

        return view('warehouse.stocks.index', compact('stocks', 'categories'));
    }

    /**
     * Show Form to Add Stock (Purchase)
     */
    public function create()
    {
        // Get active products for dropdown
        $products = Product::where('is_active', true)
            ->select('id', 'product_name', 'sku', 'unit')
            ->orderBy('product_name')
            ->get();

        $warehouseId = 1;

        return view('warehouse.stocks.create', compact('products', 'warehouseId'));
    }

    /**
     * Process Stock Addition
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:0.01',
            'unit_type'  => 'required|in:base,purchase',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date'  => 'nullable|date',
            'cost_price'   => 'nullable|numeric|min:0',
            'bin_location' => 'nullable|string|max:50', // NEW
            'remarks'      => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $warehouseId = 1;
            $finalQty = $request->quantity;
            if ($request->unit_type === 'purchase') {
                $finalQty = $request->quantity * ($product->conversion_factor ?? 1);
            }

            // 2. Prepare Batch Data
            $batchData = [
                'batch_number' => $request->batch_number,
                'exp_date'     => $request->expiry_date,
                'cost_price'   => $request->cost_price ?? ($product->cost_price ?? 0),
            ];

            // 3. Call the Model Helper (The logic we wrote earlier)
            $product->addStock(
                $warehouseId,
                $finalQty,
                'purchase', // Transaction Type
                $batchData,
                Auth::id(), // User
                $request->remarks
            );

            // 4. Update Bin Location if provided
            if ($request->filled('bin_location')) {
                ProductStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->update(['bin_location' => $request->bin_location]);
            }

            DB::commit();
            return redirect()->route('warehouse.stocks.index')
                ->with('success', "Stock Added! Total added: $finalQty {$product->unit}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adding stock: ' . $e->getMessage());
        }
    }

    /**
     * API for Frontend to get Product Units & Batch Status
     */
    public function getProductDetails(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'unit' => $product->unit,
            'purchase_unit' => $product->purchase_unit,
            'conversion_factor' => $product->conversion_factor,
            'is_batch_active' => $product->is_batch_active,
            'current_stock' => $product->stock->quantity ?? 0
        ]);
    }

    public function history(Request $request, Product $product)
    {
        $query = $product->transactions()->with(['batch', 'user']);

        // 1. Date Filter
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 2. Type Filter
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 3. User Filter
        if ($request->user_id) {
            $query->where('ware_user_id', $request->user_id);
        }

        $transactions = $query->latest()->paginate(20);

        // Fetch users for dropdown filter
        $users = WareUser::where('is_active', true)->get();

        return view('warehouse.stocks.history', compact('product', 'transactions', 'users'));
    }

    /**
     * Show Stock Adjustment Form
     */
    public function adjust()
    {
        $products = Product::where('is_active', true)
            ->select('id', 'product_name', 'sku', 'unit')
            ->orderBy('product_name')
            ->get();

        return view('warehouse.stocks.adjust', compact('products'));
    }

    /**
     * Process the Adjustment
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'action'     => 'required|in:add,subtract',
            'reason'     => 'required|in:adjustment,damage,return,theft', // Theft maps to adjustment/damage
            'quantity'   => 'required|numeric|min:0.01',
            'remarks'    => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $warehouseId = 1; // Default for now

            // Map UI "Reason" to Database "Transaction Type"
            $txnType = $request->reason;
            if ($request->reason == 'theft') $txnType = 'adjustment'; // Map theft to adjustment or damage

            if ($request->action == 'add') {
                // ADD STOCK (Correction/Return)
                // Note: For adjustments, we might not have specific batch info, 
                // so we let the system create a "General Adjustment" batch or similar if needed.
                // For simplicity here, we create a new batch for positive adjustment.

                $batchData = [
                    'batch_number' => 'ADJ-' . date('ymd-His'),
                    'cost_price'   => $product->cost_price // Use current standard cost
                ];

                $product->addStock(
                    $warehouseId,
                    $request->quantity,
                    $txnType,
                    $batchData,
                    Auth::id(),
                    $request->remarks ?? 'Manual Stock Adjustment'
                );
            } else {
                // REMOVE STOCK (Damage/Theft/Correction)
                // Uses FIFO to remove from oldest batches
                $product->removeStock(
                    $warehouseId,
                    $request->quantity,
                    $txnType,
                    Auth::id(),
                    $request->remarks ?? 'Manual Stock Deduction'
                );
            }

            DB::commit();

            $prodName = Product::find($request->product_id)->product_name;
            NotificationService::sendToAdmins(
                'Stock Adjusted',
                "Manual adjustment of {$request->quantity} for {$prodName} by " . auth()->user()->name,
                'warning',
                route('warehouse.stocks.history', $request->product_id)
            );

            return redirect()->route('warehouse.stocks.index')
                ->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Adjustment Failed: ' . $e->getMessage());
        }
    }

    public function markAsDamaged(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:product_batches,id',
            'quantity' => 'required|numeric|min:1',
            'reason'   => 'required|string'
        ]);

        return DB::transaction(function () use ($request) {
            $batch = ProductBatch::where('id', $request->batch_id)
                ->where('warehouse_id', 1)
                ->lockForUpdate()
                ->firstOrFail();

            if ($batch->quantity < $request->quantity) {
                return back()->with('error', 'Insufficient Good Quantity to mark as damaged!');
            }

            $batch->decrement('quantity', $request->quantity);
            $batch->increment('damaged_quantity', $request->quantity);

            ProductStock::where('product_id', $batch->product_id)
                ->where('warehouse_id', 1)
                ->decrement('quantity', $request->quantity);

            StockTransaction::create([
                'warehouse_id' => 1,
                'product_id'   => $batch->product_id,
                'type'         => 'adjustment',
                'quantity_change' => - ($request->quantity),
                'running_balance' => ProductStock::where('product_id', $batch->product_id)->value('quantity'),
                'ware_user_id' => Auth::id(),
                'remarks'      => 'Marked as Damaged: ' . $request->reason . ' (Batch: ' . $batch->batch_number . ')'
            ]);

            return back()->with('success', 'Stock marked as damaged successfully.');
        });
    }
}
