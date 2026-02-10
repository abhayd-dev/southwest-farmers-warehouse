<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareNotification;
use App\Models\WareUser;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        // 1. Get the first user (Usually Super Admin)
        $user = WareUser::first();

        if (!$user) {
            $this->command->info('No users found in ware_users table. Skipping...');
            return;
        }

        // 2. Define 5 Test Notifications
        $notifications = [
            [
                'user_id' => $user->id,
                'title'   => 'New Stock Request',
                'message' => 'Store "North Branch" has requested 50 units of Wireless Mouse.',
                'type'    => 'info',
                'url'     => '/warehouse/stock-requests',
                'read_at' => null, // Unread
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'user_id' => $user->id,
                'title'   => '⚠️ Low Stock Warning',
                'message' => 'Product "Gaming Keyboard" dropped below minimum level (Qty: 5).',
                'type'    => 'warning',
                'url'     => '/warehouse/stock-control/minmax',
                'read_at' => null, // Unread
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'user_id' => $user->id,
                'title'   => '✅ PO Received',
                'message' => 'Purchase Order #PO-2026-001 from "Tech Vendors Ltd" has been successfully received.',
                'type'    => 'success',
                'url'     => '/warehouse/purchase-orders',
                'read_at' => Carbon::now()->subHours(2), // Read
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $user->id,
                'title'   => '🚨 Critical: Late Delivery',
                'message' => 'PO #PO-2026-005 is delayed by 3 days. Vendor not responding.',
                'type'    => 'danger',
                'url'     => '/warehouse/purchase-orders',
                'read_at' => null, // Unread
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'user_id' => $user->id,
                'title'   => 'Support Ticket Reply',
                'message' => 'Store Manager replied to Ticket #TKT-889: "Thanks for the update."',
                'type'    => 'info',
                'url'     => '/warehouse/support',
                'read_at' => Carbon::now()->subDays(2), // Read
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];

        // 3. Insert into Database
        foreach ($notifications as $n) {
            WareNotification::create($n);
        }

        $this->command->info('5 Test Notifications seeded for user: ' . $user->email);
    }
}