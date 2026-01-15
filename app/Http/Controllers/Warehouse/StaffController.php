<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WareUser;
use App\Models\WareRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = WareUser::with('roles');

        // Global Search
        $query->when($request->search, function ($q) use ($request) {
            $s = $request->search;
            $q->where(function ($sub) use ($s) {
                $sub->where('name', 'ilike', "%$s%")
                    ->orWhere('email', 'ilike', "%$s%")
                    ->orWhere('emp_code', 'ilike', "%$s%")
                    ->orWhere('phone', 'ilike', "%$s%");
            });
        });

        // Filter by Role
        $query->when($request->role_id, function ($q) use ($request) {
            $q->whereHas('roles', function ($r) use ($request) {
                $r->where('id', $request->role_id);
            });
        });

        $staff = $query->latest()->paginate(10);
        
        // Get roles for filter dropdown
        $roles = WareRole::where('guard_name', 'web')->get();

        return view('warehouse.staff.index', compact('staff', 'roles'));
    }

    public function create()
    {
        $roles = WareRole::where('guard_name', 'web')->get();
        return view('warehouse.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'emp_code' => 'required|string|unique:ware_users,emp_code',
            'email' => 'required|email|unique:ware_users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8',
            'role' => 'required|exists:ware_roles,name',
            'profile_image' => 'nullable|image|max:2048', // 2MB Max
            'address' => 'nullable|string',
            'designation' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['role', 'profile_image', 'password']);
            $data['password'] = Hash::make($request->password);
            $data['is_active'] = true;

            // Handle Image Upload
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('staff_images', 'public');
            }

            $user = WareUser::create($data);

            // Assign Role using Name (Safe approach)
            $role = WareRole::where('name', $request->role)->first();
            if($role) {
                $user->roles()->sync([$role->id]);
            }

            DB::commit();
            return redirect()->route('warehouse.staff.index')->with('success', 'Staff Member Created Successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = WareUser::findOrFail($id);
        $roles = WareRole::where('guard_name', 'web')->get();
        $userRole = $user->roles->first()?->name;
        
        return view('warehouse.staff.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, $id)
    {
        $user = WareUser::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'emp_code' => 'required|string|unique:ware_users,emp_code,'.$user->id,
            'email' => 'required|email|unique:ware_users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:ware_roles,name',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['role', 'profile_image', 'password']);

            // Update Password only if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Handle Image
            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $data['profile_image'] = $request->file('profile_image')->store('staff_images', 'public');
            }

            $user->update($data);

            // Sync Role
            $role = WareRole::where('name', $request->role)->first();
            if($role) {
                $user->roles()->sync([$role->id]);
            }

            DB::commit();
            return redirect()->route('warehouse.staff.index')->with('success', 'Staff Member Updated Successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = WareUser::findOrFail($id);
            if ($user->id === auth()->id()) {
                return back()->with('error', 'You cannot delete yourself!');
            }
            $user->delete(); // Soft Delete
            return back()->with('success', 'Staff Member Deleted.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}