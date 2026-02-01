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
    public function run()
    {
        $now = Carbon::now();
        $password = Hash::make('12345678');

        /*
        |--------------------------------------------------------------------------
        | Parent User (Store Owner / Admin)
        | Using first active user – NO Spatie dependency
        |--------------------------------------------------------------------------
        */
        $parentUser = StoreUser::where('is_active', true)
            ->orderBy('id')
            ->first();

        if (!$parentUser) {
            $this->command->error('No active parent user found.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Staff as per Uploaded Image (Store Operations)
        |--------------------------------------------------------------------------
        */
        $staffData = [
            ['name' => 'Regional Manager', 'email' => 'rm@store.com', 'role' => 'Regional Manager'],
            ['name' => 'General Manager',  'email' => 'gm@store.com', 'role' => 'General Manager'],
            ['name' => 'Store Manager',    'email' => 'manager@store.com', 'role' => 'Manager'],
            ['name' => 'Supervisor',       'email' => 'supervisor@store.com', 'role' => 'Supervisor'],
            ['name' => 'Cashier',          'email' => 'cashier@store.com', 'role' => 'Cashier'],
            ['name' => 'General Staff',    'email' => 'staff@store.com', 'role' => 'General Staff'],
            ['name' => 'Receptionist',     'email' => 'reception@store.com', 'role' => 'Receptionist'],
        ];

        DB::beginTransaction();

        try {
            foreach ($staffData as $staff) {

                // Skip if staff already exists
                if (StoreUser::where('email', $staff['email'])->exists()) {
                    continue;
                }

                // Get Role
                $role = StoreRole::where('name', $staff['role'])
                    ->where('guard_name', 'store_user')
                    ->first();

                if (!$role) {
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
                    'is_active'     => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Attach Role (CUSTOM – NO SPATIE)
                | store_model_has_roles requires model_type
                |--------------------------------------------------------------------------
                */
                $alreadyAssigned = DB::table('store_model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_id', $user->id)
                    ->where('model_type', StoreUser::class)
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('store_model_has_roles')->insert([
                        'role_id'    => $role->id,
                        'model_id'   => $user->id,
                        'model_type' => StoreUser::class,
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Store staff seeded successfully (password: 12345678)');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Staff Seeder Failed: ' . $e->getMessage());
        }
    }
}
