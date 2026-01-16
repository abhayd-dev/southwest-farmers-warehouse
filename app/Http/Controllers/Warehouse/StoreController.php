<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StoreDetail;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
        // Permissions can be added here
        // $this->middleware('permission:manage_stores')->only(['create', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $stores = StoreDetail::with('manager')->latest()->paginate(10);
        return view('warehouse.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('warehouse.stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            // Store Info
            'store_name' => 'required|string|max:255',
            'store_email'=> 'required|email|unique:store_details,email',
            'store_phone'=> 'required|string|max:15',
            'city'       => 'required|string',
            'state'      => 'required|string',
            'pincode'    => 'required|string',
            'address'    => 'required|string',
            
            // Manager Info
            'manager_name' => 'required|string|max:255',
            'manager_email'=> 'required|email|unique:store_users,email',
            'manager_phone'=> 'nullable|string|max:15',
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
        
        return view('warehouse.stores.show', compact('store', 'analytics'));
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
            'store_email'=> 'required|email|unique:store_details,email,'.$id,
            'store_phone'=> 'required|string|max:15',
            'city'       => 'required|string',
            'address'    => 'required|string',
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
}