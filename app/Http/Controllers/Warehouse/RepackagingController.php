<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepackagingController extends Controller
{
    /**
     * Display the Repackaging transfer form.
     */
    public function index()
    {
        $departments = Department::where('is_active', true)->get();
        $categories = ProductCategory::where('is_active', true)->get();
        $products = Product::where('is_active', true)
            ->whereNull('store_id')
            ->orderBy('product_name')
            ->get();

        return view('warehouse.stock-control.repackaging.index', compact('departments', 'categories', 'products'));
    }

    /**
     * Find associated products automatically based on product base name.
     * Example: Selecting "GHANA GARI 50LBS" returns "GHANA GARI 20LBS", "GHANA GARI 10LBSx5pcs", etc.
     */
    public function getAssociatedProducts(Product $product)
    {
        // Extract base product name (remove weight or size pattern like 50LB, 50LBS, 20LB, etc.)
        $baseName = preg_replace('/\s+\d+(LB|LBS|KG|OZ|PCS|BAG).*$/i', '', $product->product_name);
        $baseName = trim($baseName);

        // Find products with similar base name, excluding the bulk product itself
        $associated = Product::where('is_active', true)
            ->whereNull('store_id')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($baseName) {
                $q->where('product_name', 'like', "%{$baseName}%")
                  ->orWhere('sku', 'like', "%{$baseName}%");
            })
            ->limit(20)
            ->get(['id', 'product_name', 'upc', 'sku', 'unit', 'cost_price']);

        return response()->json([
            'status' => 'success',
            'base_name' => $baseName,
            'products' => $associated
        ]);
    }

    /**
     * Process Repackaging Transfer:
     * 1. Deduct source bulk product inventory
     * 2. Add target repackaged products inventory
     * 3. Log stock transactions with remark 'repackaging transfer'
     */
    public function store(Request $request)
    {
        $request->validate([
            'source_product_id' => 'required|exists:products,id',
            'source_quantity'   => 'required|numeric|min:0.01',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|numeric|min:0.01',
            'remarks'           => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $warehouseId = 1;
            $userId = Auth::id() ?? 1;
            $refId = 'REPACK-' . strtoupper(Str::random(8));
            $remarks = $request->input('remarks') ?: 'repackaging transfer';

            // 1. DEDUCT SOURCE BULK PRODUCT STOCK
            $sourceStock = ProductStock::firstOrCreate(
                ['product_id' => $request->source_product_id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $sourceStock->quantity -= $request->source_quantity;
            $sourceStock->save();

            // Create Outbound Transaction Log for Bulk Product
            StockTransaction::create([
                'product_id'      => $request->source_product_id,
                'warehouse_id'    => $warehouseId,
                'ware_user_id'    => $userId,
                'type'            => 'transfer_out',
                'quantity_change' => -$request->source_quantity,
                'running_balance' => $sourceStock->quantity,
                'reference_id'    => $refId,
                'remarks'         => $remarks,
            ]);

            // 2. ADD TARGET REPACKAGED PRODUCTS STOCK
            foreach ($request->items as $item) {
                $targetStock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $warehouseId],
                    ['quantity' => 0]
                );

                $targetStock->quantity += $item['quantity'];
                $targetStock->save();

                // Create Inbound Transaction Log for Repackaged Product
                StockTransaction::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $warehouseId,
                    'ware_user_id'    => $userId,
                    'type'            => 'transfer_in',
                    'quantity_change' => $item['quantity'],
                    'running_balance' => $targetStock->quantity,
                    'reference_id'    => $refId,
                    'remarks'         => $remarks,
                ]);
            }

            DB::commit();

            return redirect()->route('warehouse.stock-control.repackaging.index')
                ->with('success', 'Repackaging transfer completed successfully. Inventory updated with reference ' . $refId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process repackaging transfer: ' . $e->getMessage());
        }
    }
}
