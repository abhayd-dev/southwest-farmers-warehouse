<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KitchenMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Kitchen Locations
        $storeKitchen = \App\Models\KitchenLocation::create(['name' => 'Store Kitchen', 'type' => 'store']);
        $warehouseKitchen = \App\Models\KitchenLocation::create(['name' => 'Warehouse Kitchen', 'type' => 'warehouse']);

        $kitchenSub = \App\Models\KitchenLocation::create(['name' => 'Kitchen', 'type' => 'sub_kitchen', 'parent_id' => $storeKitchen->id]);
        $bakerySub = \App\Models\KitchenLocation::create(['name' => 'Bakery', 'type' => 'sub_kitchen', 'parent_id' => $storeKitchen->id]);
        
        $sauceSub = \App\Models\KitchenLocation::create(['name' => 'Sauce', 'type' => 'sub_kitchen', 'parent_id' => $warehouseKitchen->id]);
        $soupSub = \App\Models\KitchenLocation::create(['name' => 'Soup', 'type' => 'sub_kitchen', 'parent_id' => $warehouseKitchen->id]);

        // 2. Create Menu Categories
        $foodCat = \App\Models\MenuCategory::create(['name' => 'South West Farmers Market Food Menu', 'kitchen_location_id' => $kitchenSub->id]);
        $soupsCat = \App\Models\MenuCategory::create(['name' => 'Soups', 'kitchen_location_id' => $kitchenSub->id]);
        $bakeryCat = \App\Models\MenuCategory::create(['name' => 'Bakery & Pastries', 'kitchen_location_id' => $bakerySub->id]);

        // 3. Populate 39 Items
        $foodItems = [
            'WHITE RICE', 'YAM PORRIDGE', 'DESIGNER STEW', 'JOLLOF RICE', 'COCONUT RICE', 
            'MOI-MOI', 'FRIED RICE', 'SPAGHETTI', 'BEANS AND STEW', 'BEANS PORRIDGE', 
            'ASUN', 'EFO-RIRO', 'PLANTAIN PUDDING', 'MOI-MOI', 'AKARA', 'BUNS', 'EGG-ROLL', 
            'PUFF-PUFF', 'BEEF AND KPOMO', 'FISH ROLL', 'MEAT PIE', 'WHITE BREAD', 
            'WHEAT BREAD', 'FRIED CHICKEN', 'FRIED TURKEY', 'STEWED BEEF', 'FRIED FISH', 
            'BAKED FISH', 'FRIED YAM', 'FRIED PLANTAIN'
        ];

        // Move bakery items to bakery category
        $bakeryItems = ['BUNS', 'EGG-ROLL', 'PUFF-PUFF', 'FISH ROLL', 'MEAT PIE', 'WHITE BREAD', 'WHEAT BREAD'];

        foreach ($foodItems as $item) {
            if (in_array($item, $bakeryItems)) {
                \App\Models\MenuItem::create(['name' => $item, 'menu_category_id' => $bakeryCat->id]);
            } else {
                \App\Models\MenuItem::create(['name' => $item, 'menu_category_id' => $foodCat->id]);
            }
        }

        $soupItems = [
            'EGUSI', 'OGBONO', 'OHA', 'AFANG', 'BANGA', 'VEGETABLES', 'EDIKAIKON', 'NSALA', 'RED STEW'
        ];

        foreach ($soupItems as $item) {
            \App\Models\MenuItem::create(['name' => $item, 'menu_category_id' => $soupsCat->id]);
        }
    }
}
