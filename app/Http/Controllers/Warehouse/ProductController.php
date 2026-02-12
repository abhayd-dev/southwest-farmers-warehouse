<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductStock;
use App\Models\Department;
use App\Imports\ProductImport;
use App\Exports\ProductExport;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            // FILTER: Only show Warehouse Products (store_id IS NULL)
            $products = Product::whereNull('store_id')
                ->with(['category', 'subcategory', 'option', 'department'])
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
                'options' => ProductOption::where('is_active', 1)->get(),
                'categories' => ProductCategory::whereNull('store_id')->where('is_active', 1)->get(),
                'departments' => Department::where('is_active', true)->get(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to open create page' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'department_id' => 'required|exists:departments,id',
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'product_name' => 'required',
                'unit' => 'required',
                'price' => 'required|numeric',
                'barcode' => 'required|string|unique:products,barcode|max:255', // Changed to required and unique
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
            'options' => ProductOption::where('is_active', 1)->get(),
            'categories' => ProductCategory::whereNull('store_id')->where('is_active', 1)->get(),
            'subcategories' => ProductSubcategory::whereNull('store_id')->where('category_id', $product->category_id)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->store_id !== null) abort(403);

        try {
            $request->validate([
                'department_id' => 'required|exists:departments,id',
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'product_name' => 'required',
                'unit' => 'required',
                'price' => 'required|numeric',
                'barcode' => 'required|string|max:255|unique:products,barcode,' . $product->id, // Added unique check ignoring current id
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
                'department_id' => 'required|exists:departments,id',
                'category_id' => 'required',
                'subcategory_id' => 'required',
            ]);
            
            // Pass department_id to Import Class (Assuming Import Class constructor is updated)
            Excel::import(new ProductImport($request->category_id, $request->subcategory_id, $request->department_id), $request->file);
            
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

    public function generateBarcode()
    {
        // Generate a random 13 digit number or unique string
        // Checking for uniqueness
        do {
            $barcode = mt_rand(1000000000000, 9999999999999);
        } while (Product::where('barcode', $barcode)->exists());

        return response()->json(['barcode' => $barcode]);
    }

    public function bulkPriceUpdate(Request $request)
    {
        set_time_limit(300); 
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'percentage' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $query = Product::whereNull('store_id')
                ->where('category_id', $request->category_id);

            if ($request->subcategory_id) {
                $query->where('subcategory_id', $request->subcategory_id);
            }

            $count = $query->count();
            if ($count === 0) {
                return back()->with('error', 'No products found matching the selection.');
            }

            // Calculate Factor (e.g., 10% -> 1.10)
            $factor = 1 + ($request->percentage / 100);
            
            // PostgreSQL Raw Update
            $query->update(['price' => DB::raw("price * $factor")]);

            // Optional: Notification
            $catName = ProductCategory::find($request->category_id)->name;
            $msg = "Bulk Price Update: Increased by {$request->percentage}% for Category: $catName";
            if($request->subcategory_id) {
                $subName = ProductSubcategory::find($request->subcategory_id)->name;
                $msg .= ", Subcategory: $subName";
            }
            
            NotificationService::sendToAdmins('Price Update', $msg, 'info');

            DB::commit();

            return back()->with('success', "Updated prices for $count products successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update prices: ' . $e->getMessage());
        }
    }
}