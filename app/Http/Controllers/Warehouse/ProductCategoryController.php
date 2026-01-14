<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Imports\ProductCategoryImport;
use App\Exports\ProductCategoryExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::withCount('subcategories')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('code', 'ilike', "%{$request->search}%");
            })
            ->when($request->status !== null, function ($q) use ($request) {
                $q->where('is_active', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('warehouse.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('warehouse.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_categories,code',
            'icon' => 'nullable|image|max:2048', // Validation
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        ProductCategory::create($data);

        return redirect()->route('warehouse.categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit(ProductCategory $category)
    {
        return view('warehouse.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_categories,code,' . $category->id,
            'icon' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            // Delete old icon
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully');
    }

    public function destroy(ProductCategory $category)
    {
        try {
            if ($category->subcategories()->exists()) {
                return back()->with('error', 'Cannot delete category with associated subcategories.');
            }
            
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->delete();
            return back()->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed');
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            ProductCategory::findOrFail($request->id)
                ->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        Excel::import(new ProductCategoryImport, $request->file);
        return back()->with('success', 'Imported successfully');
    }

    public function export()
    {
        return Excel::download(new ProductCategoryExport, 'categories.xlsx');
    }
    
    public function sample()
    {
         return response()->download(storage_path('app/samples/categories-sample.xlsx'));
    }
}