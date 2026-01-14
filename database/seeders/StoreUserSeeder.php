<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('store_users')->updateOrInsert(
            // UNIQUE KEY (email)
            ['email' => 'store@gmail.com'],

            // DATA TO INSERT / UPDATE
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('12345678'), // default password
                'updated_at' => now(),
                'created_at' => now(), // ignored on update
            ]
        );
    }
}
