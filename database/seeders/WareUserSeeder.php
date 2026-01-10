<?php

namespace Database\Seeders;

use App\Models\WareUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WareUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        WareUser::create([
            'name' => 'Warehouse Admin',
            'email' => 'warehouse@admin.com',
            'password' => bcrypt('password'),
        ]);
    }
}
