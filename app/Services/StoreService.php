<?php

namespace App\Services;

use App\Models\StoreDetail;
use App\Models\StoreUser;
use App\Models\StoreStock;
use App\Models\StoreRole;
use App\Models\StoreOrderSchedule;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StoreService
{
    public function createStore(array $data)
    {
        return DB::transaction(function () use ($data) {
            $storeCode = 'SWF-' . strtoupper(substr($data['city'], 0, 3)) . '-' . rand(100, 999);

            $store = StoreDetail::create([
                'warehouse_id' => 1,
                'store_name'   => $data['store_name'],
                'store_code'   => $storeCode,
                'email'        => $data['store_email'],
                'phone'        => $data['store_phone'],
                'address'      => $data['address'],
                'city'         => $data['city'],
                'state'        => $data['state'],
                'pincode'      => $data['pincode'],
                'latitude'     => $data['latitude'] ?? null,
                'longitude'    => $data['longitude'] ?? null,
                'is_active'    => true,
                'store_group_id' => ! empty($data['store_group_id']) ? (int) $data['store_group_id'] : null,
            ]);

            $managerRole = StoreRole::where('name', 'Super Admin')->orWhere('name', 'Manager')->first();

            $manager = StoreUser::create([
                'store_id'      => $store->id,
                'store_role_id' => $managerRole ? $managerRole->id : null,
                'name'          => $data['manager_name'],
                'email'         => $data['manager_email'],
                'password'      => Hash::make($data['password']),
                'phone'         => $data['manager_phone'] ?? null,
                'is_active'     => true,
            ]);

            $store->update(['store_user_id' => $manager->id]);
            $this->grantStoreRoles($manager, [$manager->store_role_id]);

            if (!empty($data['market_id'])) {
                $store->markets()->sync([$data['market_id']]);
            }

            return $store;
        });
    }

    public function updateStore(StoreDetail $store, array $data)
    {
        return DB::transaction(function () use ($store, $data) {
            $store->update($data);
            
            if (isset($data['market_id'])) {
                if (!empty($data['market_id'])) {
                    $store->markets()->sync([$data['market_id']]);
                } else {
                    $store->markets()->detach();
                }
            }
            
            return $store;
        });
    }

    /**
     * Hand the store's manager position to another staff member of the same
     * store. The new manager gets the manager's role (so the same access);
     * the previous manager keeps their account and role untouched.
     */
    public function reassignManager(StoreDetail $store, int $storeUserId): StoreUser
    {
        return DB::transaction(function () use ($store, $storeUserId) {
            $newManager = StoreUser::where('store_id', $store->id)->findOrFail($storeUserId);

            $managerRoleId = StoreUser::whereKey($store->store_user_id)->value('store_role_id')
                ?? StoreRole::where('name', 'Super Admin')->orWhere('name', 'Manager')->value('id');

            // The store app checks access through store_model_has_roles, so copy
            // every role the current manager holds there, not just the column.
            $managerRoleIds = DB::table('store_model_has_roles')
                ->where('model_type', StoreUser::class)
                ->where('model_id', $store->store_user_id)
                ->pluck('role_id')
                ->push($managerRoleId)
                ->all();

            if ($managerRoleId) {
                $newManager->update(['store_role_id' => $managerRoleId]);
            }
            $this->grantStoreRoles($newManager, $managerRoleIds);
            $store->update(['store_user_id' => $newManager->id]);

            return $newManager;
        });
    }

    public function updateStoreSchedule($storeId, array $data)
    {
        return StoreOrderSchedule::updateOrCreate(
            ['store_id' => $storeId],
            [
                'expected_day'      => $data['expected_day'],
                'time_window_start' => $data['order_time'] ?? '09:00:00', // Mapping order_time to start window
                'cutoff_time'       => '17:00:00', // Default cutoff
                'is_active'         => isset($data['is_active']),
            ]
        );
    }

    public function createStoreStaff($storeId, array $data)
    {
        return DB::transaction(function () use ($storeId, $data) {
            $staff = StoreUser::create([
                'store_id'      => $storeId,
                'store_role_id' => $data['store_role_id'],
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'password'      => Hash::make($data['password']),
                'is_active'     => true,
            ]);
            $this->grantStoreRoles($staff, [$staff->store_role_id]);

            return $staff;
        });
    }

    /**
     * Add roles to a store user in store_model_has_roles (what the store app
     * reads for permissions). Existing roles are kept; duplicates are skipped.
     *
     * @param array<int|null> $roleIds
     */
    private function grantStoreRoles(StoreUser $user, array $roleIds): void
    {
        $rows = collect($roleIds)->filter()->unique()
            ->map(fn ($roleId) => ['role_id' => (int) $roleId, 'model_type' => StoreUser::class, 'model_id' => $user->id])
            ->values()->all();

        if ($rows) {
            DB::table('store_model_has_roles')->insertOrIgnore($rows);
        }
    }

    public function deleteStoreStaff($staffId)
    {
        $user = StoreUser::findOrFail($staffId);
        if ($user->isStoreAdmin()) {
            throw new \App\Exceptions\BusinessRuleException('Cannot delete the Main Store Manager.');
        }
        $user->delete();
    }

    public function getAnalyticsData($storeId, array $filters)
    {
        // 1. Base Query
        // CRITICAL FIX: Added 'sale' to the array to catch Store 6 transactions
        $query = StockTransaction::join('products', 'stock_transactions.product_id', '=', 'products.id')
            ->where('stock_transactions.store_id', $storeId)
            ->whereIn('stock_transactions.type', ['sale', 'sale_out']); 

        // 2. Date Range Filter
        if (!empty($filters['date_range'])) {
            $dates = explode(' to ', $filters['date_range']);
            if (count($dates) === 2) {
                $query->whereBetween('stock_transactions.created_at', [
                    Carbon::parse($dates[0])->startOfDay(),
                    Carbon::parse($dates[1])->endOfDay(),
                ]);
            } else {
                // Default: Last 30 Days if format is wrong
                $query->whereBetween('stock_transactions.created_at', [
                    Carbon::now()->subDays(30)->startOfDay(),
                    Carbon::now()->endOfDay(),
                ]);
            }
        }

        // 3. Product Type Filter
        if (!empty($filters['product_type']) && $filters['product_type'] !== 'all') {
            if ($filters['product_type'] === 'warehouse') {
                $query->whereNull('products.store_id');
            } elseif ($filters['product_type'] === 'store') {
                $query->where('products.store_id', $storeId);
            }
        }

        // 4. Category Filter (Numeric Check Prevents 500 Error)
        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        // 5. Subcategory Filter
        if (!empty($filters['subcategory_id']) && is_numeric($filters['subcategory_id'])) {
            $query->where('products.subcategory_id', $filters['subcategory_id']);
        }

        // 6. Specific Product Filter
        if (!empty($filters['product_id']) && is_numeric($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        // --- Data Aggregation ---

        // A. Sales Trend (Line Chart)
        $trendQuery = clone $query;
        $trendData = $trendQuery
            ->select(
                DB::raw('DATE(stock_transactions.created_at) as date'),
                DB::raw('SUM(ABS(stock_transactions.quantity_change)) as total_qty')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // B. Product Performance (Bar Chart)
        $productQuery = clone $query;
        $productData = $productQuery
            ->select(
                'products.product_name as name', 
                DB::raw('SUM(ABS(stock_transactions.quantity_change)) as total_qty')
            )
            ->groupBy('products.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // C. Category Distribution (Pie Chart)
        $catQuery = clone $query;
        $catData = $catQuery
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.name',
                DB::raw('SUM(ABS(stock_transactions.quantity_change)) as total_qty')
            )
            ->groupBy('product_categories.name')
            ->orderByDesc('total_qty')
            ->get();

        $totalSales = $trendData->sum('total_qty');

        return [
            'sales_trend' => [
                'labels' => $trendData->pluck('date'),
                'data'   => $trendData->pluck('total_qty')->map(fn($v) => (float)$v),
            ],
            'product_performance' => [
                'labels' => $productData->pluck('name'),
                'data'   => $productData->pluck('total_qty')->map(fn($v) => (float)$v),
            ],
            'category_distribution' => [
                'labels' => $catData->pluck('name'),
                'data'   => $catData->pluck('total_qty')->map(fn($v) => (float)$v),
            ],
            'totals' => [
                'sales'   => (float)$totalSales,
                'revenue' => 0, 
            ],
        ];
    }

    public function getStoreStats($storeId)
    {
        return [
            'inventory_value' => StoreStock::where('store_id', $storeId)->sum(DB::raw('selling_price * quantity')),
            'inventory_items' => StoreStock::where('store_id', $storeId)->count(),
            'low_stock_count' => StoreStock::where('store_id', $storeId)->where('quantity', '<', 10)->count(),
            'staff_count'     => StoreUser::where('store_id', $storeId)->count(),
        ];
    }
}