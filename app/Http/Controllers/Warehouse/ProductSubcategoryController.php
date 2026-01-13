<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ProductSubcategory;
use App\Models\ProductCategory;
use App\Imports\ProductSubcategoryImport;
use App\Exports\ProductSubcategoryExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $subcategories = ProductSubcategory::with('category')
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where('name', 'ilike', "%$s%")
                    ->orWhere('code', 'ilike', "%$s%")
                    ->orWhereHas('category', fn($c) => $c->where('name', 'ilike', "%$s%"));
            })
            ->when($request->status !== null, fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(10);

        $categories = ProductCategory::active()->orderBy('name')->get();

        return view('warehouse.subcategories.index', compact('subcategories', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::active()->get();
        return view('warehouse.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_subcategories,code',
        ]);

        ProductSubcategory::create($request->all());
        return redirect()->route('warehouse.subcategories.index')->with('success', 'Subcategory created successfully');
    }

    public function edit(ProductSubcategory $subcategory)
    {
        $categories = ProductCategory::active()->get();
        return view('warehouse.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, ProductSubcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_subcategories,code,' . $subcategory->id,
        ]);

        $subcategory->update($request->all());
        return back()->with('success', 'Subcategory updated successfully');
    }

    public function destroy(ProductSubcategory $subcategory)
    {
        try {
            if ($subcategory->productOptions()->exists()) {
                return back()->with('error', 'Cannot delete: Subcategory has associated Product Options.');
            }

            if ($subcategory->products()->exists()) {
                return back()->with('error', 'Cannot delete: Subcategory has associated Products.');
            }

            $subcategory->delete();

            return back()->with('success', 'Subcategory deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
    public function changeStatus(Request $request)
    {
        try {
            ProductSubcategory::findOrFail($request->id)->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'category_id' => 'required|exists:product_categories,id' 
        ]);

        Excel::import(new ProductSubcategoryImport($request->category_id), $request->file);
        
        return back()->with('success', 'Imported successfully');
    }

    public function export()
    {
        return Excel::download(new ProductSubcategoryExport, 'subcategories.xlsx');
    }

    public function sample()
    {
        return response()->download(storage_path('app/samples/subcategories-sample.xlsx'));
    }
}
