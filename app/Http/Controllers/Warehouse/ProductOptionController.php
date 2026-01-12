<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ProductOption;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Imports\ProductOptionImport;
use App\Exports\ProductOptionExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ProductOptionController extends Controller
{
    /**
     * LIST
     */
    public function index(Request $request)
    {
        try {
            $options = ProductOption::with(['category', 'subcategory'])
                ->when($request->search, function ($q) use ($request) {
                    $search = $request->search;

                    $q->where('option_name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                        ->orWhereHas(
                            'category',
                            fn($c) =>
                            $c->where('name', 'ilike', "%{$search}%")
                        )
                        ->orWhereHas(
                            'subcategory',
                            fn($s) =>
                            $s->where('name', 'ilike', "%{$search}%")
                        );
                })
                ->when($request->status !== null, function ($q) use ($request) {
                    $q->where('is_active', $request->status);
                })
                ->latest()
                ->paginate(10);

            $categories = ProductCategory::where('is_active', true)->get();

            return view('warehouse.product-options.index', compact('options', 'categories'));
        } catch (\Exception $e) {
            Log::error('ProductOption Index Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load product options.');
        }
    }

    /**
     * CREATE
     */
    public function create()
    {
        try {
            $categories = ProductCategory::where('is_active', true)->get();

            return view('warehouse.product-options.create', compact('categories'));
        } catch (\Exception $e) {
            Log::error('ProductOption Create Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to open create form.');
        }
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'option_name'   => 'required|string|max:255',
                'sku'           => 'required|string|max:100|unique:product_options,sku',
                'category_id'   => 'required|exists:product_categories,id',
                'subcategory_id' => 'required|exists:product_subcategories,id',
                'unit'          => 'required',
            ]);

            ProductOption::create([
                'option_name'   => $request->option_name,
                'sku'           => $request->sku,
                'category_id'   => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'unit'          => $request->unit,
                'tax_percent'   => $request->tax_percent,
                'cost_price'    => $request->cost_price,
                'base_price'    => $request->base_price,
                'mrp'           => $request->mrp,
                'is_active'     => 1,
            ]);

            return redirect()
                ->route('warehouse.product-options.index')
                ->with('success', 'Product option created successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    /**
     * EDIT
     */
    public function edit(ProductOption $productOption)
    {
        try {
            $categories = ProductCategory::where('is_active', true)->get();
            $subcategories = ProductSubcategory::where('category_id', $productOption->category_id)
                ->where('is_active', true)
                ->get();

            return view(
                'warehouse.product-options.edit',
                compact('productOption', 'categories', 'subcategories')
            );
        } catch (\Exception $e) {
            Log::error('ProductOption Edit Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to open edit page.');
        }
    }

    /**
     * UPDATE
     */
    public function update(Request $request, ProductOption $productOption)
    {
        try {
            $request->validate([
                'option_name'   => 'required|string|max:255',
                'sku'           => 'required|string|max:100|unique:product_options,sku,' . $productOption->id,
                'category_id'   => 'required|exists:product_categories,id',
                'subcategory_id' => 'required|exists:product_subcategories,id',
                'unit'          => 'required',
            ]);

            $productOption->update([
                'option_name'   => $request->option_name,
                'sku'           => $request->sku,
                'category_id'   => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'unit'          => $request->unit,
                'tax_percent'   => $request->tax_percent,
                'cost_price'    => $request->cost_price,
                'base_price'    => $request->base_price,
                'mrp'           => $request->mrp,
            ]);

            return back()->with('success', 'Product option updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    /**
     * DELETE
     */
    public function destroy(ProductOption $productOption)
    {
        try {
            $productOption->delete();

            return back()->with('success', 'Product option deleted successfully.');
        } catch (\Exception $e) {
            Log::error('ProductOption Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete product option.');
        }
    }

    /**
     * STATUS TOGGLE (AJAX)
     */
    public function changeStatus(Request $request)
    {
        try {
            $option = ProductOption::findOrFail($request->id);

            $option->update([
                'is_active' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('ProductOption Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * IMPORT
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'category_id'    => 'required|exists:product_categories,id',
                'subcategory_id' => 'nullable|exists:product_subcategories,id',
                'file'           => 'required|mimes:xlsx,csv',
            ]);

            Excel::import(
                new ProductOptionImport(
                    $request->category_id,
                    $request->subcategory_id
                ),
                $request->file
            );

            return back()->with('success', 'Product options imported successfully.');
        } catch (\Exception $e) {
            Log::error('ProductOption Import Error: ' . $e->getMessage());
            return back()->with('error', 'Import failed. Please check file format.');
        }
    }

    /**
     * EXPORT
     */
    public function export()
    {
        try {
            return Excel::download(
                new ProductOptionExport,
                'product-options.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('ProductOption Export Error: ' . $e->getMessage());
            return back()->with('error', 'Export failed.');
        }
    }

    /**
     * SAMPLE FILE
     */
    public function sample()
    {
        try {
            return response()->download(
                storage_path('app/samples/product-options-sample.xlsx')
            );
        } catch (\Exception $e) {
            Log::error('ProductOption Sample Error: ' . $e->getMessage());
            return back()->with('error', 'Sample file not found.');
        }
    }

    /**
     * FETCH SUBCATEGORIES (AJAX)
     */
    public function fetchSubcategories(ProductCategory $category)
    {
        try {
            $subcategories = ProductSubcategory::where('category_id', $category->id)
                ->where('is_active', true)
                ->get();

            return response()->json($subcategories);
        } catch (\Exception $e) {
            Log::error('Fetch Subcategory Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
