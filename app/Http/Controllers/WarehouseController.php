<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->search) {
            $query->where('warehouse_name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $warehouses = $query->latest()->paginate(10);

        return view('warehouse.index', compact('warehouses'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'warehouse_name' => 'required|string|max:255',
            'code'           => 'required|string|max:50',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
        ]);

        $warehouse->update($request->all());

        return back()->with('success', 'Warehouse updated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $warehouse = Warehouse::findOrFail($request->id);
        $warehouse->update(['is_active' => $request->status]);

        return response()->json([
            'status'  => true,
            'message' => 'Warehouse status updated successfully.',
        ]);
    }
}
