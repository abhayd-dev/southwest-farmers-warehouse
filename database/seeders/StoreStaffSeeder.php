<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\StoreUser;
use App\Models\StoreRole;

class StoreStaffSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('12345678');

        /*
        |--------------------------------------------------------------------------
        | Parent User (Super Admin / Store Owner)
        |--------------------------------------------------------------------------
        | Assumption:
        | - Super Admin already exists
        | - store_role_id mapped correctly
        |--------------------------------------------------------------------------
        */
        $parentUser = StoreUser::where('is_active', true)
            ->orderBy('id')
            ->first();

        if (!$parentUser) {
            $this->command->error('No active parent user found. Please seed store_users first.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Staff Data (STRICTLY based on Permission Seeder Roles)
        |--------------------------------------------------------------------------
        */
        $staffData = [
            [
                'name'  => 'Store Manager',
                'email' => 'manager@store.com',
                'role'  => 'Store Manager',
            ],
            [
                'name'  => 'Inventory Manager',
                'email' => 'inventory@store.com',
                'role'  => 'Inventory Manager',
            ],
            [
                'name'  => 'Cashier',
                'email' => 'cashier@store.com',
                'role'  => 'Cashier',
            ],
            [
                'name'  => 'Sales Staff',
                'email' => 'sales@store.com',
                'role'  => 'Sales Staff',
            ],
        ];

        DB::beginTransaction();

        try {

            foreach ($staffData as $staff) {

                /*
                |--------------------------------------------------------------------------
                | Skip if user already exists
                |--------------------------------------------------------------------------
                */
                if (StoreUser::where('email', $staff['email'])->exists()) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Get Role (STRICT match)
                |--------------------------------------------------------------------------
                */
                $role = StoreRole::where('name', $staff['role'])
                    ->where('guard_name', 'store_user')
                    ->first();

                if (!$role) {
                    $this->command->warn("Role not found: {$staff['role']}");
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Create Staff User
                |--------------------------------------------------------------------------
                */
                $user = StoreUser::create([
                    'parent_id'     => $parentUser->id,
                    'store_id'      => $parentUser->store_id,
                    'name'          => $staff['name'],
                    'email'         => $staff['email'],
                    'phone'         => null,
                    'password'      => $password,
                    'store_role_id' => $role->id,
                    'is_active'     => true,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Attach Role (Custom RBAC – NO SPATIE)
                |--------------------------------------------------------------------------
                */
                DB::table('store_model_has_roles')->insert([
                    'role_id'    => $role->id,
                    'model_id'   => $user->id,
                    'model_type' => StoreUser::class,
                ]);
            }

            DB::commit();
            $this->command->info('Store staff seeded successfully (password: 12345678)');

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->command->error('StoreStaffSeeder failed: ' . $e->getMessage());
        }
    }
}
