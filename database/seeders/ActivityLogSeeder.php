<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareActivityLog;
use App\Models\WareUser;
use App\Models\Product;
use App\Models\Vendor;
use Faker\Factory as Faker;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Get some existing users and models to link
        $users = WareUser::all();
        $products = Product::limit(10)->get();
        $vendors = Vendor::limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->info('No users found, skipping logs seeding.');
            return;
        }

        $actions = ['created', 'updated', 'deleted', 'login'];

        // Create 50 Dummy Logs
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $action = $faker->randomElement($actions);
            
            // Randomly choose a subject (Product, Vendor, or User)
            $subjectType = null;
            $subjectId = null;
            $description = '';
            $properties = [];

            if ($action === 'login') {
                $description = "User logged in";
                $subjectType = get_class($user);
                $subjectId = $user->id;
            } else {
                // Randomly pick a target model
                $target = $faker->randomElement(['product', 'vendor']);
                
                if ($target == 'product' && $products->isNotEmpty()) {
                    $prod = $products->random();
                    $subjectType = get_class($prod);
                    $subjectId = $prod->id;
                    $description = ucfirst($action) . " product: " . $prod->product_name;
                    
                    if ($action === 'updated') {
                        $properties = [
                            'old' => ['price' => $faker->randomFloat(2, 10, 100)],
                            'new' => ['price' => $faker->randomFloat(2, 100, 200)]
                        ];
                    }
                } elseif ($target == 'vendor' && $vendors->isNotEmpty()) {
                    $vend = $vendors->random();
                    $subjectType = get_class($vend);
                    $subjectId = $vend->id;
                    $description = ucfirst($action) . " vendor: " . $vend->name;
                }
            }

            WareActivityLog::create([
                'causer_id'    => $user->id,
                'causer_type'  => get_class($user),
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'action'       => $action,
                'description'  => $description,
                'properties'   => $properties,
                'ip_address'   => $faker->ipv4,
                'user_agent'   => $faker->userAgent,
                'created_at'   => $faker->dateTimeBetween('-1 month', 'now'),
            ]);
        }
    }
}