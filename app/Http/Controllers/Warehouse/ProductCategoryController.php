<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Imports\CategoryImport;
use App\Exports\ProductCategoryExport;
use App\Exports\Samples\CategorySampleExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportTask;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        // FILTER: Only Warehouse Categories (store_id IS NULL)
        $categories = ProductCategory::whereNull('store_id')
            ->withCount('subcategories')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('name', 'ilike', "%{$request->search}%")
                          ->orWhere('code', 'ilike', "%{$request->search}%");
                });
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
            'icon' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['store_id'] = null; // Explicit isolation

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'r2');
        }

        ProductCategory::create($data);

        return redirect()->route('warehouse.categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit(ProductCategory $category)
    {
        if ($category->store_id !== null) abort(403);
        return view('warehouse.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        if ($category->store_id !== null) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:product_categories,code,' . $category->id,
            'icon' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('r2')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('categories', 'r2');
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->store_id !== null) abort(403);

        try {
            if ($category->subcategories()->exists()) {
                return back()->with('error', 'Cannot delete category with associated subcategories.');
            }
            
            if ($category->icon) {
                Storage::disk('r2')->delete($category->icon);
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
            ProductCategory::whereNull('store_id')->findOrFail($request->id)
                ->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }
            
            // Create Import Task
            $task = ImportTask::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'type' => 'Category',
                'status' => ImportTask::STATUS_PENDING,
                'file_name' => $request->file('file')->getClientOriginalName(),
            ]);

            Excel::import(
                new CategoryImport(\Illuminate\Support\Facades\Auth::id(), $task->id), 
                $request->file
            );

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import started!',
                    'task_id' => $task->id
                ]);
            }

            return back()->with('success', 'Import started! You will be notified once processing is complete.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new ProductCategoryExport, 'categories.xlsx');
    }
    
    public function sample()
    {
         return Excel::download(new CategorySampleExport, 'categories-sample.xlsx');
    }
}