<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareUser;
use Illuminate\Support\Facades\Hash;

class WareUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Super Admin (Already exists usually, but ensuring)
        $this->createUser('Super Admin', 'admin@warehouse.com', 'Super Admin');

        // 2. Executives
        $this->createUser('Elon CEO', 'ceo@warehouse.com', 'CEO');
        $this->createUser('Satya CFO', 'cfo@warehouse.com', 'CFO');
        $this->createUser('Tim VP Ops', 'vp@warehouse.com', 'VP Operations');

        // 3. Managers
        $this->createUser('Penny Purchase', 'purchase@warehouse.com', 'Purchase Manager');
        $this->createUser('Ian Inventory', 'inventory@warehouse.com', 'Inventory Manager');
        $this->createUser('Reggie Regional', 'regional@warehouse.com', 'Regional Manager');
        $this->createUser('Greg General', 'general@warehouse.com', 'General Manager');
        
        // 4. Finance
        $this->createUser('Adam Accountant', 'accountant@warehouse.com', 'Accountant');
        $this->createUser('Fiona Finance', 'finance@warehouse.com', 'Finance Assistant');

        // 5. Operations
        $this->createUser('Steve Supervisor', 'supervisor@warehouse.com', 'Supervisor');
        $this->createUser('Harry Handler', 'handler@warehouse.com', 'Store Handler');
        $this->createUser('Charlie Cashier', 'cashier@warehouse.com', 'Cashier');
        $this->createUser('Gary General', 'staff@warehouse.com', 'General Staff');
    }

    private function createUser($name, $email, $role)
    {
        $user = WareUser::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('12345678'),
                'phone' => '9876543210',
                'designation' => $role,
                'is_active' => true,
            ]
        );

        // Assign Role using Spatie/Your Role logic
        // Assuming your WareUser model has a helper or you use traits
        // If using standard spatie: $user->assignRole($role);
        // Using your custom logic implied in context:
        $roleModel = \App\Models\WareRole::where('name', $role)->first();
        if ($roleModel) {
            // Detach all current roles to be safe
            $user->roles()->detach();
            $user->roles()->attach($roleModel->id);
        }
    }
}