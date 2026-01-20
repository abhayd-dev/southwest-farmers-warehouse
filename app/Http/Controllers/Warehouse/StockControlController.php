<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StoreStock;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class StockControlController extends Controller
{
    public function overview()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $subcategories = ProductSubcategory::where('is_active', true)->get();
        return view('warehouse.stock-control.overview', compact('categories', 'subcategories'));
    }

    public function overviewData(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'subcategory'])
            ->select('products.*')
            ->addSelect([
                'warehouse_qty' => ProductStock::selectRaw('coalesce(sum(quantity - reserved_quantity - damaged_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('warehouse_id', 1)
                    ->limit(1),
                'total_stores_qty' => StoreStock::selectRaw('coalesce(sum(quantity - reserved_quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
            ]);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->low_stock) {
            $query->havingRaw('(warehouse_qty + total_stores_qty) < 10');
        }

        return DataTables::of($query)
            ->addColumn('category_name', fn($row) => $row->category->name ?? '-')
            ->addColumn('subcategory_name', fn($row) => $row->subcategory->name ?? '-')
            ->addColumn('total_qty', fn($row) => $row->warehouse_qty + $row->total_stores_qty)
            ->addColumn('value', fn($row) => number_format(($row->warehouse_qty + $row->total_stores_qty) * $row->cost_price, 2))
            ->addColumn('action', fn($row) => '
                <a href="' . route('warehouse.stocks.history', $row->id) . '" class="btn btn-sm btn-info">History</a>
                <a href="#" class="btn btn-sm btn-warning">Adjust</a>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function expiryDamage()
    {
        return view('warehouse.stock-control.expiry');
    }

    public function valuation()
    {
        return view('warehouse.stock-control.valuation');
    }
}