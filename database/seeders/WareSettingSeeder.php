<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareSetting;

class WareSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'Southwest Farmers Warehouse', 'type' => 'text'],
            ['key' => 'app_phone', 'value' => '+1 234 567 890', 'type' => 'text'],
            ['key' => 'support_email', 'value' => 'support@example.com', 'type' => 'email'],
            ['key' => 'app_address', 'value' => 'New York, USA', 'type' => 'textarea'],
            ['key' => 'main_logo', 'value' => null, 'type' => 'image'],
            ['key' => 'favicon', 'value' => null, 'type' => 'image'],
            ['key' => 'login_logo', 'value' => null, 'type' => 'image'],
        ];

        foreach ($settings as $setting) {
            WareSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}