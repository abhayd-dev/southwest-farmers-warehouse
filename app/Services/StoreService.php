<?php

namespace App\Services;

use App\Models\StoreDetail;
use App\Models\StoreUser;
use App\Models\StoreStock;
use App\Models\StoreRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreService
{
    /**
     * Create Store with Map Coordinates and Manager
     */
    public function createStore(array $data)
    {
        return DB::transaction(function () use ($data) {
            $storeCode = $this->generateStoreCode($data['city']);

            // Create Store
            $store = StoreDetail::create([
                'warehouse_id' => 1, // Default warehouse
                'store_name' => $data['store_name'],
                'store_code' => $storeCode,
                'email'      => $data['store_email'],
                'phone'      => $data['store_phone'],
                'address'    => $data['address'],
                'city'       => $data['city'],
                'state'      => $data['state'],
                'country'    => $data['country'] ?? 'India',
                'pincode'    => $data['pincode'],
                'latitude'   => $data['latitude'] ?? null,
                'longitude'  => $data['longitude'] ?? null,
                'is_active'  => true,
            ]);

            // Find Manager Role ID
            $managerRole = StoreRole::where('name', 'Super Admin')->orWhere('name', 'Manager')->first();
            $roleId = $managerRole ? $managerRole->id : null;

            // Create Manager
            $manager = StoreUser::create([
                'store_id'      => $store->id, 
                'store_role_id' => $roleId,
                'name'          => $data['manager_name'],
                'email'         => $data['manager_email'],
                'password'      => Hash::make($data['password']),
                'phone'         => $data['manager_phone'] ?? null,
                'is_active'     => true,
            ]);

            // Link Manager to Store Record
            $store->update(['store_user_id' => $manager->id]);

            return $store;
        });
    }

    /**
     * Update Store Details
     */
    public function updateStore(StoreDetail $store, array $data)
    {
        return DB::transaction(function () use ($store, $data) {
            $store->update([
                'store_name' => $data['store_name'],
                'email'      => $data['store_email'],
                'phone'      => $data['store_phone'],
                'address'    => $data['address'],
                'city'       => $data['city'],
                'state'      => $data['state'],
                'pincode'    => $data['pincode'],
                'latitude'   => $data['latitude'] ?? $store->latitude,
                'longitude'  => $data['longitude'] ?? $store->longitude,
                'is_active'  => $data['is_active'] ?? $store->is_active,
            ]);
            return $store;
        });
    }

    /**
     * Create Staff Member
     */
    public function createStoreStaff($storeId, array $data)
    {
        return DB::transaction(function () use ($storeId, $data) {
            return StoreUser::create([
                'store_id'      => $storeId,
                'store_role_id' => $data['store_role_id'],
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'password'      => Hash::make($data['password']),
                'is_active'     => true,
            ]);
        });
    }

    public function deleteStoreStaff($staffId)
    {
        $user = StoreUser::findOrFail($staffId);
        if ($user->isStoreAdmin()) {
            throw new \Exception("Cannot delete the Main Store Manager. Reassign manager first.");
        }
        $user->delete();
    }

    /**
     * Generate Unique Store Code
     */
    private function generateStoreCode($city)
    {
        $prefix = 'SWF-' . strtoupper(substr($city, 0, 3));
        $latest = StoreDetail::where('store_code', 'like', "$prefix%")->latest()->first();
        
        if ($latest) {
            $parts = explode('-', $latest->store_code);
            $number = intval(end($parts)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get Real-time Analytics for Dashboard
     */
    public function getStoreAnalytics($storeId)
    {
        // 1. Basic Stats
        $stats = [
            'staff_count'     => StoreUser::where('store_id', $storeId)->count(),
            'inventory_items' => StoreStock::where('store_id', $storeId)->count(),
            'inventory_value' => StoreStock::where('store_id', $storeId)->sum(DB::raw('selling_price * quantity')),
            'low_stock_count' => StoreStock::where('store_id', $storeId)->where('quantity', '<', 10)->count(),
        ];

        $categoryData = StoreStock::join('products', 'store_stocks.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('store_stocks.store_id', $storeId)
            ->select('product_categories.name', DB::raw('SUM(store_stocks.quantity) as total_qty'))
            ->groupBy('product_categories.name')
            ->get();

        // 3. Mock Sales Trends (Replace with Order/Sales table later)
        $salesTrends = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'data'   => [12000, 19000, 3000, 5000, 2000, 30000, 45000] 
        ];

        return array_merge($stats, [
            'categories' => $categoryData,
            'sales_trends' => $salesTrends
        ]);
    }
}