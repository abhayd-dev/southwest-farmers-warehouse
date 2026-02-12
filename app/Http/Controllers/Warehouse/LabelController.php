<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LabelController extends Controller
{
    public function printPallet(Request $request)
    {
        $data = [];

        if ($request->has('transaction_id')) {
            $transaction = StockTransaction::with(['product', 'product.category'])->findOrFail($request->transaction_id);
            
            $data = [
                'pallet_id' => 'PLT-' . $transaction->id . '-' . mt_rand(1000, 9999), // Unique Pallet ID
                'product_name' => $transaction->product->product_name,
                'sku' => $transaction->product->sku,
                'category' => $transaction->product->category->name ?? 'General',
                'quantity' => abs($transaction->quantity_change),
                'unit' => $transaction->product->unit,
                'batch_no' => $transaction->reference_id ?? 'BATCH-' . date('ymd'),
                'expiry' => $transaction->product->shelf_life_days 
                            ? Carbon::now()->addDays($transaction->product->shelf_life_days)->format('d M Y') 
                            : 'N/A',
                'date' => now()->format('d/m/Y H:i'),
                'weight' => $transaction->product->box_weight 
                            ? ($transaction->product->box_weight * abs($transaction->quantity_change)) . ' kg' 
                            : 'N/A',
                'product_barcode' => $transaction->product->barcode,            
            ];
        } 
        elseif ($request->has('product_id')) {
            // Manual Label Generation from Product List
            $product = Product::with('category')->findOrFail($request->product_id);
            $qty = $request->qty ?? 1;

            $data = [
                'pallet_id' => 'PLT-' . $product->id . '-' . time(),
                'product_name' => $product->product_name,
                'sku' => $product->sku,
                'category' => $product->category->name ?? 'General',
                'quantity' => $qty,
                'unit' => $product->unit,
                'batch_no' => 'MANUAL-' . date('ymd'),
                'expiry' => $product->shelf_life_days 
                            ? Carbon::now()->addDays($product->shelf_life_days)->format('d M Y') 
                            : 'N/A',
                'date' => now()->format('d/m/Y H:i'),
                'weight' => $product->box_weight ? ($product->box_weight * $qty) . ' kg' : 'N/A',
                'product_barcode' => $product->barcode,
            ];
        }
        else {
            abort(404, 'No data source provided');
        }

        return view('warehouse.labels.pallet', compact('data'));
    }
}