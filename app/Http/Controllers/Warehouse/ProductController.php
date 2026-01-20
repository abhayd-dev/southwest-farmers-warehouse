<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductStock;
use App\Imports\ProductImport;
use App\Exports\ProductExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            // FILTER: Only show Warehouse Products (store_id IS NULL)
            $products = Product::whereNull('store_id')
                ->with(['category', 'subcategory', 'option'])
                ->when($request->search, function ($q) use ($request) {
                    $s = $request->search;
                    $q->where(function($query) use ($s) {
                        $query->where('product_name', 'ilike', "%$s%")
                            ->orWhere('sku', 'ilike', "%$s%")
                            ->orWhere('barcode', 'ilike', "%$s%")
                            ->orWhereHas('category', fn($c) => $c->where('name', 'ilike', "%$s%"))
                            ->orWhereHas('subcategory', fn($s2) => $s2->where('name', 'ilike', "%$s%"));
                    });
                })
                ->when($request->status !== null, fn($q) => $q->where('is_active', $request->status))
                ->latest()
                ->paginate(10);

            // Filter Categories for the search dropdown
            $categories = ProductCategory::whereNull('store_id')->where('is_active', true)->get();

            return view('warehouse.products.index', compact('products', 'categories'));
        } catch (\Exception $e) {
            Log::error($e);
            return back()->with('error', 'Failed to load products');
        }
    }

    public function create()
    {
        try {
            return view('warehouse.products.create', [
                // Only Warehouse Options & Categories
                'options' => ProductOption::where('is_active', 1)->get(),
                'categories' => ProductCategory::whereNull('store_id')->where('is_active', 1)->get(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to open create page' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'product_name' => 'required',
                'unit' => 'required',
                'price' => 'required|numeric',
                'barcode' => 'nullable|string|max:255',
                'icon' => 'nullable|image|max:2048',
            ]);

            return DB::transaction(function () use ($request) {
                $data = $request->except('icon');
                
                // Explicitly set store_id to NULL for Warehouse Products
                $data['store_id'] = null; 

                if ($request->hasFile('icon')) {
                    $data['icon'] = $request->file('icon')->store('products', 'public');
                }

                // If creating a new option on the fly
                if (!$request->product_option_id) {
                    $option = ProductOption::create([
                        'ware_user_id' => auth()->id(),
                        'store_id' => null, // Warehouse Option
                        'option_name' => $request->product_name,
                        'sku' => $request->sku,
                        'category_id' => $request->category_id,
                        'subcategory_id' => $request->subcategory_id,
                        'unit' => $request->unit,
                        'barcode' => $request->barcode,
                        'tax_percent' => 0,
                        'cost_price' => $request->price,
                        'base_price' => $request->price,
                        'mrp' => $request->price,
                    ]);
                    $data['product_option_id'] = $option->id;
                }

                $product = Product::create($data);

                // Initialize Warehouse Stock
                ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => 1,
                    'quantity' => 0
                ]);

                return redirect()->route('warehouse.products.index')
                    ->with('success', 'Product created successfully');
            });

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        // Security Check: Ensure product belongs to Warehouse
        if ($product->store_id !== null) {
            abort(403, 'Unauthorized access to store product');
        }

        return view('warehouse.products.edit', [
            'product' => $product,
            'options' => ProductOption::whereNull('store_id')->where('is_active', 1)->get(),
            'categories' => ProductCategory::whereNull('store_id')->where('is_active', 1)->get(),
            'subcategories' => ProductSubcategory::whereNull('store_id')->where('category_id', $product->category_id)->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->store_id !== null) abort(403);

        try {
            $request->validate([
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'product_name' => 'required',
                'unit' => 'required',
                'price' => 'required|numeric',
                'barcode' => 'nullable|string|max:255',
                'icon' => 'nullable|image|max:2048',
            ]);

            $data = $request->except('icon');

            if ($request->hasFile('icon')) {
                if ($product->icon && Storage::disk('public')->exists($product->icon)) {
                    Storage::disk('public')->delete($product->icon);
                }
                $data['icon'] = $request->file('icon')->store('products', 'public');
            }

            $product->update($data);

            return back()->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        if ($product->store_id !== null) abort(403);

        try {
            if ($product->icon && Storage::disk('public')->exists($product->icon)) {
                Storage::disk('public')->delete($product->icon);
            }
            $product->delete();
            return back()->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed');
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $product = Product::whereNull('store_id')->findOrFail($request->id);
            $product->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status'], 500);
        }
    }

    public function fetchOption(ProductOption $option)
    {
        // Ensure option is warehouse level
        if ($option->store_id !== null) abort(403);
        return response()->json($option);
    }

    public function fetchSubcategories($categoryId)
    {
        // Filter Subcategories: store_id IS NULL
        $subcategories = ProductSubcategory::whereNull('store_id')
            ->where('category_id', $categoryId)
            ->where('is_active', 1)
            ->get();
        return response()->json($subcategories);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv',
                'category_id' => 'required',
                'subcategory_id' => 'required',
            ]);
            Excel::import(new ProductImport($request->category_id, $request->subcategory_id), $request->file);
            return back()->with('success', 'Imported successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }

    public function sample()
    {
        return response()->download(storage_path('app/samples/products-sample.xlsx'));
    }
}