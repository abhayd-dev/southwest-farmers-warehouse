<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KitchenModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Menu Categories
        $catRice = \App\Models\MenuCategory::firstOrCreate(['name' => 'Rice Dishes']);
        $catSoups = \App\Models\MenuCategory::firstOrCreate(['name' => 'Soups & Stews']);
        $catSnacks = \App\Models\MenuCategory::firstOrCreate(['name' => 'Snacks & Pastries']);
        $catProteins = \App\Models\MenuCategory::firstOrCreate(['name' => 'Proteins']);

        // 2. Create Menu Items
        $jollof = \App\Models\MenuItem::firstOrCreate(['name' => 'Jollof Rice'], [
            'menu_category_id' => $catRice->id,
            'price' => 15.00,
            'is_pre_cooked' => true,
            'daily_target_quantity' => 100,
            'is_catering_only' => false,
        ]);

        $egusi = \App\Models\MenuItem::firstOrCreate(['name' => 'Egusi Soup'], [
            'menu_category_id' => $catSoups->id,
            'price' => 18.00,
            'is_pre_cooked' => true,
            'daily_target_quantity' => 50,
        ]);

        $meatPie = \App\Models\MenuItem::firstOrCreate(['name' => 'Meat Pie'], [
            'menu_category_id' => $catSnacks->id,
            'price' => 5.00,
            'is_pre_cooked' => true,
            'daily_target_quantity' => 200,
        ]);

        $cateringSpecial = \App\Models\MenuItem::firstOrCreate(['name' => 'Large Party Jollof Tray'], [
            'menu_category_id' => $catRice->id,
            'price' => 150.00,
            'is_pre_cooked' => false,
            'is_catering_only' => true,
            'advance_notice_days' => 5,
            'rush_fee_percentage' => 20,
        ]);

        // 3. Create a Recipe for Jollof
        $jollofRecipe = \App\Models\Recipe::firstOrCreate(['menu_item_id' => $jollof->id], [
            'name' => 'Standard Jollof Rice Batch',
            'description' => 'The standard recipe for a large batch of Jollof.',
            'prep_time_minutes' => 30,
            'cook_time_minutes' => 60,
        ]);

        \App\Models\RecipeStep::firstOrCreate(['recipe_id' => $jollofRecipe->id, 'step_number' => 1], [
            'instruction' => 'Blend tomatoes, peppers, and onions.',
            'timer_minutes' => 15,
        ]);
        
        \App\Models\RecipeStep::firstOrCreate(['recipe_id' => $jollofRecipe->id, 'step_number' => 2], [
            'instruction' => 'Fry the blended paste in oil until water reduces completely.',
            'timer_minutes' => 25,
        ]);

        // 4. Kitchen Locations
        $locMain = \App\Models\KitchenLocation::firstOrCreate(['name' => 'Warehouse Main Kitchen']);
        $locStore = \App\Models\KitchenLocation::firstOrCreate(['name' => 'Store Front Display']);

        // 5. Staff (WareUsers) - We will just use first 2 users if they exist, else create
        $user1 = \App\Models\WareUser::firstOrCreate(['email' => 'chef1@example.com'], [
            'name' => 'Chef Gordon',
            'password' => bcrypt('password'),
            'designation' => 'Head Chef',
        ]);
        
        $user2 = \App\Models\WareUser::firstOrCreate(['email' => 'staff1@example.com'], [
            'name' => 'Line Cook Alex',
            'password' => bcrypt('password'),
            'designation' => 'Line Cook',
        ]);

        // 6. Production Logs (Yesterday & Today)
        \App\Models\KitchenProduction::create([
            'menu_item_id' => $jollof->id,
            'ware_user_id' => $user1->id,
            'kitchen_location_id' => $locMain->id,
            'quantity_made' => 50,
            'quantity_unit' => 'plates',
            'yield_plates' => 50,
            'daily_target' => 100,
            'notes' => 'Morning batch of Jollof',
            'produced_at' => now()->subHours(5),
        ]);

        // 7. Leftover Logs (Yesterday)
        \App\Models\LeftoverLog::create([
            'menu_item_id' => $jollof->id,
            'kitchen_location_id' => $locStore->id,
            'quantity_produced' => 50,
            'quantity_sold' => 45,
            'quantity_leftover' => 5,
            'log_date' => now()->subDays(1)->toDateString(),
            'notes' => 'End of day spoilage',
        ]);

        // 8. Staff Shifts for Today
        \App\Models\KitchenShift::create([
            'ware_user_id' => $user1->id,
            'kitchen_location_id' => $locMain->id,
            'shift_date' => now()->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'station' => 'Main Kitchen',
            'status' => 'scheduled',
        ]);

        // 9. Time Log (Clock In)
        \App\Models\KitchenTimeLog::create([
            'ware_user_id' => $user1->id,
            'kitchen_location_id' => $locMain->id,
            'clock_in_at' => now()->subHours(2),
            'status' => 'clocked_in',
        ]);
    }
}
