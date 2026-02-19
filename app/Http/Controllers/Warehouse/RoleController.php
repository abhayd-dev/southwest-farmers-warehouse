<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WareRole;
use App\Models\WarePermission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roles = WareRole::withCount(['users', 'permissions'])
            ->when($search, function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%"); // Postgres usage
            })
            ->paginate(10);
            
        return view('warehouse.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = WarePermission::all()->groupBy(function($item) {
            // Group by category based on naming convention (e.g., view_products -> Products)
            $parts = explode('_', $item->name);
            if (count($parts) > 1) {
                // If starts with view, manage, create, etc. take the second part
                if (in_array($parts[0], ['view', 'manage', 'create', 'edit', 'delete', 'approve', 'reject', 'adjust', 'receive', 'export'])) {
                     return ucfirst($parts[1]);
                }
            }
            return 'General';
        });

        return view('warehouse.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:ware_roles,name',
            'permissions' => 'array'
        ]);

        $role = WareRole::create(['name' => $request->name, 'guard_name' => 'web']);
        
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('warehouse.roles.index')->with('success', 'Role created successfully');
    }

    public function edit(WareRole $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('warehouse.roles.index')->with('error', 'Super Admin role cannot be edited');
        }

        $permissions = WarePermission::all()->groupBy(function($item) {
             $parts = explode('_', $item->name);
            if (count($parts) > 1) {
                if (in_array($parts[0], ['view', 'manage', 'create', 'edit', 'delete', 'approve', 'reject', 'adjust', 'receive', 'export'])) {
                     return ucfirst($parts[1]);
                }
            }
            return 'General';
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('warehouse.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, WareRole $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Super Admin role cannot be edited');
        }

        $request->validate([
            'name' => 'required|unique:ware_roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
             $role->permissions()->detach();
        }

        return redirect()->route('warehouse.roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(WareRole $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Super Admin role cannot be deleted');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role assigned to users');
        }

        $role->delete();
        return redirect()->route('warehouse.roles.index')->with('success', 'Role deleted successfully');
    }
}
