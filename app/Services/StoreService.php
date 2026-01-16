<?php

namespace App\Services;

use App\Models\StoreDetail;
use App\Models\StoreUser;
use App\Models\StoreStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreService
{
    /**
     * Create a new Store and its Super Admin Manager in a single transaction.
     */
    public function createStore(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Generate Store Code (SWF-LKO-001)
            $storeCode = $this->generateStoreCode($data['city']);

            // 2. Create Store Entry
            $store = StoreDetail::create([
                'warehouse_id' => 1, // Defaulting to 1 for now
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

            // 3. Create Store Manager (Super Admin)
            $manager = StoreUser::create([
                'name'        => $data['manager_name'],
                'email'       => $data['manager_email'],
                'password'    => Hash::make($data['password']),
                'phone'       => $data['manager_phone'] ?? null,
                'designation' => 'Store Manager',
                'is_active'   => true,
            ]);

            // 4. Link Manager to Store
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
        return [
            'staff_count'     => 0, 
            'inventory_items' => StoreStock::where('store_id', $storeId)->count(),
            // FIXED: Used 'selling_price' instead of 'price'
            'inventory_value' => StoreStock::where('store_id', $storeId)->sum(DB::raw('selling_price * quantity')),
            'low_stock_count' => StoreStock::where('store_id', $storeId)->where('quantity', '<', 10)->count(),
            'revenue_mtd'     => 0, 
        ];
    }
}