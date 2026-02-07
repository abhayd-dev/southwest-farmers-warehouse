<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareSetting;

class WareSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'description' => 'Global minimum stock level for email alerts'
            ],
            [
                'key' => 'alert_emails',
                'value' => 'admin@warehouse.com', // Change this to your real email
                'description' => 'Comma separated emails for notifications'
            ],
            [
                'key' => 'enable_low_stock_email',
                'value' => '1',
                'description' => '1 = Enable, 0 = Disable'
            ],
            [
                'key' => 'enable_late_po_email',
                'value' => '1',
                'description' => '1 = Enable, 0 = Disable'
            ]
        ];

        foreach ($settings as $s) {
            WareSetting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}