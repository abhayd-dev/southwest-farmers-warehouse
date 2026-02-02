<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['name' => 'Meat & Poultry', 'code' => 'MEAT'],
            ['name' => 'Fresh Produce', 'code' => 'PRODUCE'],
            ['name' => 'Dairy & Eggs', 'code' => 'DAIRY'],
            ['name' => 'Frozen Foods', 'code' => 'FROZEN'],
            ['name' => 'Bakery', 'code' => 'BAKERY'],
            ['name' => 'Pantry & Dry Goods', 'code' => 'PANTRY'],
            ['name' => 'Beverages', 'code' => 'BEV'],
            ['name' => 'Household & Cleaning', 'code' => 'HOUSE'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}