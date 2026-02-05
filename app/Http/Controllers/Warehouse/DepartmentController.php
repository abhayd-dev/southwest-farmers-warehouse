<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->search) {
            $query->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('code', 'ilike', "%{$request->search}%");
        }

        if ($request->status !== null) {
            $query->where('is_active', $request->status);
        }

        $departments = $query->latest()->paginate(10);

        return view('warehouse.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('warehouse.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        Department::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'is_active' => true
        ]);

        return redirect()->route('warehouse.departments.index')
            ->with('success', 'Department created successfully');
    }

    public function edit(Department $department)
    {
        return view('warehouse.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
        ]);

        $department->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('warehouse.departments.index')
            ->with('success', 'Department updated successfully');
    }

    public function destroy(Department $department)
    {
        // Check if products exist in this department
        if ($department->products()->exists()) {
            return back()->with('error', 'Cannot delete department. It has associated products.');
        }

        $department->delete();
        return back()->with('success', 'Department deleted successfully');
    }

    public function changeStatus(Request $request)
    {
        try {
            Department::where('id', $request->id)->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status'], 500);
        }
    }
}