<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareUser;
use App\Models\WareRole;

class AssignSuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the Existing Admin User
        $user = WareUser::where('email', 'warehouse@admin.com')->first();

        // 2. Find the Super Admin Role
        $role = WareRole::where('name', 'Super Admin')->first();

        if ($user && $role) {
            // 3. Assign the Role (syncWithoutDetaching ensures we don't duplicate)
            $user->roles()->syncWithoutDetaching([$role->id]);
            
            $this->command->info("Success: 'Super Admin' role assigned to {$user->email}");
        } else {
            $this->command->error("Error: User or Role not found. Please check if they exist.");
        }
    }
}