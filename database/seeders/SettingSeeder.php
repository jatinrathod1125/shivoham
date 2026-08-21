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

            // Financial & Orders
            ['key' => 'currency_symbol', 'value' => '$', 'group' => 'orders', 'type' => 'string'],
            ['key' => 'currency_code', 'value' => 'USD', 'group' => 'orders', 'type' => 'string'],
            ['key' => 'tax_rate_percentage', 'value' => '8.00', 'group' => 'orders', 'type' => 'decimal'],
            ['key' => 'free_shipping_threshold', 'value' => '40.00', 'group' => 'orders', 'type' => 'decimal'],
            ['key' => 'default_shipping_fee', 'value' => '4.99', 'group' => 'orders', 'type' => 'decimal'],

            // Inventory & Alerts
            ['key' => 'default_low_stock_threshold', 'value' => '10', 'group' => 'inventory', 'type' => 'integer'],
            ['key' => 'enable_stock_alert_emails', 'value' => '1', 'group' => 'inventory', 'type' => 'boolean'],
            ['key' => 'allow_backorders', 'value' => '0', 'group' => 'inventory', 'type' => 'boolean'],

            // Business Operations
            ['key' => 'store_status', 'value' => 'online', 'group' => 'operations', 'type' => 'string'],
            ['key' => 'delivery_slots', 'value' => json_encode(['08:00 - 11:00', '11:00 - 14:00', '14:00 - 17:00', '17:00 - 20:00']), 'group' => 'operations', 'type' => 'json'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
