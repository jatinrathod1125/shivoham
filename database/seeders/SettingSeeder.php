<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General & Store
            ['key' => 'store_name', 'value' => 'Fresh Groceries Hub', 'group' => 'general', 'type' => 'string'],
            ['key' => 'store_tagline', 'value' => 'Your Everyday Organic Grocery Partner', 'group' => 'general', 'type' => 'string'],
            ['key' => 'store_email', 'value' => 'support@grocery.local', 'group' => 'general', 'type' => 'string'],
            ['key' => 'store_phone', 'value' => '+1 (800) 555-GROCERY', 'group' => 'general', 'type' => 'string'],
            ['key' => 'store_address', 'value' => '100 Market Square, Suite 400, Chicago, IL 60601', 'group' => 'general', 'type' => 'string'],

            // Currency & Formats
            ['key' => 'currency_symbol', 'value' => '$', 'group' => 'orders', 'type' => 'string'],
            ['key' => 'currency_code', 'value' => 'USD', 'group' => 'orders', 'type' => 'string'],

            // Inventory & Stock
            ['key' => 'default_low_stock_threshold', 'value' => '10', 'group' => 'inventory', 'type' => 'integer'],
            ['key' => 'allow_backorders', 'value' => '0', 'group' => 'inventory', 'type' => 'boolean'],

            // Business Operations
            ['key' => 'store_status', 'value' => 'online', 'group' => 'operations', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
