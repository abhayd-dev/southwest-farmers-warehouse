<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMinMaxLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MinMaxController extends Controller
{
    public function index()
    {
        // Get products for the dropdown
        $products = Product::where('is_active', true)
            ->whereNull('store_id')
            ->select('id', 'product_name', 'sku')
            ->get();
            
        return view('warehouse.stock-control.minmax.index', compact('products'));
    }

    public function data(Request $request)
    {
        $query = Product::query()
            ->leftJoin('product_min_max_levels', 'products.id', '=', 'product_min_max_levels.product_id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('products.is_active', true)
            ->whereNull('products.store_id')
            ->select([
                'products.id', // This is the Product ID
                'products.product_name',
                'products.sku',
                'product_categories.name as category_name',
                DB::raw('COALESCE(product_min_max_levels.min_level, 5) as min_level'),
                DB::raw('COALESCE(product_min_max_levels.max_level, 100) as max_level'),
                DB::raw('COALESCE(product_min_max_levels.reorder_quantity, 20) as reorder_qty'),
                // Simplified Current Qty logic for performance
                DB::raw('COALESCE((SELECT SUM(quantity) FROM product_stocks WHERE product_id = products.id), 0) as current_qty')
            ]);

        return DataTables::of($query)
            ->addColumn('status', function ($row) {
                $qty = $row->current_qty;
                if ($qty < $row->min_level) return '<span class="badge bg-danger">Low Stock</span>';
                if ($qty > $row->max_level) return '<span class="badge bg-warning">Overstocked</span>';
                return '<span class="badge bg-success">Optimal</span>';
            })
            ->addColumn('action', function ($row) {
                // Pass the Product ID as data-id
                return '
                    <button class="btn btn-sm btn-outline-primary edit-minmax" 
                            data-id="' . $row->id . '" 
                            data-min="' . $row->min_level . '"
                            data-max="' . $row->max_level . '"
                            data-reorder="' . $row->reorder_qty . '">
                        <i class="mdi mdi-pencil"></i> Edit
                    </button>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id|unique:product_min_max_levels,product_id',
            'min_level' => 'required|integer|min:0',
            'max_level' => 'required|integer|min:1|gte:min_level',
            'reorder_quantity' => 'required|integer|min:1',
        ]);

        ProductMinMaxLevel::create([
            'product_id' => $request->product_id,
            'min_level' => $request->min_level,
            'max_level' => $request->max_level,
            'reorder_quantity' => $request->reorder_quantity,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Min-Max levels saved successfully']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'min_level' => 'required|integer|min:0',
            'max_level' => 'required|integer|min:1|gte:min_level',
            'reorder_quantity' => 'required|integer|min:1',
        ]);

        // Use updateOrCreate to handle cases where the user edits a product 
        // that appeared in the list (via left join) but didn't have a rule yet.
        ProductMinMaxLevel::updateOrCreate(
            ['product_id' => $id],
            [
                'min_level' => $request->min_level,
                'max_level' => $request->max_level,
                'reorder_quantity' => $request->reorder_quantity,
                'updated_by' => Auth::id(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Min-Max levels updated successfully']);
    }
}