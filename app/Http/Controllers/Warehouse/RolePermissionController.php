<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WareRole;
use App\Models\WarePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = WareRole::withCount('permissions')->paginate('10');
        return view('warehouse.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = WarePermission::all()->groupBy(function($item) {
            return explode('_', $item->name)[1] ?? 'General'; // Group by 'inventory', 'po' etc.
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
        if($request->permissions) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('warehouse.roles.index')->with('success', 'Role Created');
    }

    public function edit(WareRole $role)
    {
        $permissions = WarePermission::all()->groupBy(function($item) {
            return explode('_', $item->name)[1] ?? 'General';
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('warehouse.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, WareRole $role)
    {
        $request->validate([
            'name' => 'required|unique:ware_roles,name,'.$role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);
        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('warehouse.roles.index')->with('success', 'Role Updated');
    }

    public function destroy(WareRole $role)
    {
        $role->delete();
        return back()->with('success', 'Role Deleted');
    }
}