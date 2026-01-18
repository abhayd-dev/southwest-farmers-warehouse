<?php

namespace App\Services;

use App\Models\StoreDetail;
use App\Models\StoreUser;
use App\Models\StoreStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreService
{
    public function createStore(array $data)
    {
        return DB::transaction(function () use ($data) {
            $storeCode = $this->generateStoreCode($data['city']);

            $store = StoreDetail::create([
                'warehouse_id' => 1,
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

            $manager = StoreUser::create([
                'store_id'    => $store->id, 
                'name'        => $data['manager_name'],
                'email'       => $data['manager_email'],
                'password'    => Hash::make($data['password']),
                'phone'       => $data['manager_phone'] ?? null,
                'designation' => 'Store Manager',
                'is_active'   => true,
            ]);

            $this->assignRoleToUser($manager->id, 'Super Admin');

            $store->update(['store_user_id' => $manager->id]);

            return $store;
        });
    }

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

    public function createStoreStaff($storeId, array $data)
    {
        return DB::transaction(function () use ($storeId, $data) {
            $user = StoreUser::create([
                'store_id'    => $storeId,
                'name'        => $data['name'],
                'email'       => $data['email'],
                'phone'       => $data['phone'] ?? null,
                'password'    => Hash::make($data['password']),
                'designation' => $data['role_name'], 
                'is_active'   => true,
            ]);

            $this->assignRoleToUser($user->id, $data['role_name']);

            return $user;
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

    private function assignRoleToUser($userId, $roleName)
    {
        $role = DB::table('store_roles')->where('name', $roleName)->first();
        
        if ($role) {
            DB::table('store_model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => \App\Models\StoreUser::class,
                'model_id' => $userId
            ]);
        }
    }

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

    public function getStoreAnalytics($storeId)
    {
        return [
            'staff_count'     => StoreUser::where('store_id', $storeId)->count(),
            'inventory_items' => StoreStock::where('store_id', $storeId)->count(),
            'inventory_value' => StoreStock::where('store_id', $storeId)->sum(DB::raw('selling_price * quantity')),
            'low_stock_count' => StoreStock::where('store_id', $storeId)->where('quantity', '<', 10)->count(),
            'revenue_mtd'     => 0, 
        ];
    }
}