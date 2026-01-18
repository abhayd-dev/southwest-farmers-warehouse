<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StoreDetail;
use App\Models\StoreUser;
use App\Models\StoreRole;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function index(Request $request)
    {
        $query = StoreDetail::with('manager');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', "%$search%")
                  ->orWhere('store_code', 'like', "%$search%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $stores = $query->latest()->paginate(10);
        $stores->appends($request->all());

        $cities = StoreDetail::select('city')->distinct()->orderBy('city')->pluck('city');

        return view('warehouse.stores.index', compact('stores', 'cities'));
    }

    public function create()
    {
        return view('warehouse.stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_email' => 'required|email|unique:store_details,email',
            'store_phone' => 'required|string|max:15',
            'city'       => 'required|string',
            'state'      => 'required|string',
            'pincode'    => 'required|string',
            'address'    => 'required|string',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'manager_name' => 'required|string|max:255',
            'manager_email' => 'required|email|unique:store_users,email',
            'manager_phone' => 'nullable|string|max:15',
            'password'     => 'required|min:8|confirmed',
        ]);

        try {
            $this->storeService->createStore($request->all());
            return redirect()->route('warehouse.stores.index')
                ->with('success', 'Store registered successfully with Manager account.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating store: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $store = StoreDetail::with('manager')->findOrFail($id);
        $analytics = $this->storeService->getStoreAnalytics($id);
        
        // Fetch Staff with Roles
        $staffMembers = StoreUser::with('role')
            ->where('store_id', $id)
            ->latest()
            ->get();
            
        // FIXED: Changed guard_name to 'store_user'
        $roles = StoreRole::where('guard_name', 'store_user')->get();

        return view('warehouse.stores.show', compact('store', 'analytics', 'staffMembers', 'roles'));
    }

    public function edit($id)
    {
        $store = StoreDetail::findOrFail($id);
        return view('warehouse.stores.edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        $store = StoreDetail::findOrFail($id);
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_email' => 'required|email|unique:store_details,email,' . $id,
            'store_phone' => 'required|string|max:15',
            'city'       => 'required|string',
            'address'    => 'required|string',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        try {
            $this->storeService->updateStore($store, $request->all());
            return redirect()->route('warehouse.stores.index')->with('success', 'Store details updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $store = StoreDetail::findOrFail($id);
            $store->delete();
            return redirect()->route('warehouse.stores.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete store.');
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:store_details,id',
                'status' => 'required|boolean'
            ]);

            $store = StoreDetail::findOrFail($request->id);
            $store->is_active = $request->status;
            $store->save();

            if ($store->store_user_id) {
                $manager = StoreUser::find($store->store_user_id);
                if ($manager) {
                    $manager->is_active = $request->status;
                    $manager->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Store status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating status.'], 500);
        }
    }

    public function storeStaff(Request $request, $storeId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:store_users,email',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|min:8',
            'store_role_id' => 'required|exists:store_roles,id'
        ]);

        try {
            $this->storeService->createStoreStaff($storeId, $request->all());
            return back()->with('success', 'Staff member added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding staff: ' . $e->getMessage());
        }
    }

    public function destroyStaff($id)
    {
        try {
            $this->storeService->deleteStoreStaff($id);
            return back()->with('success', 'Staff removed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}