<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'hotel_name', 'value' => 'Grand Capstone Hotel', 'group' => 'general'],
            ['key' => 'tax_rate', 'value' => '11', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'IDR', 'group' => 'general'],
            ['key' => 'estimated_delivery', 'value' => '30', 'group' => 'order'],
            ['key' => 'contact_phone', 'value' => '+6281234567890', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'info@grandcapstone.com', 'group' => 'contact'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
