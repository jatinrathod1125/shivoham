<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'FRESH10',
                'description' => '10% discount on all fresh produce orders over $25.',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 10.00,
                'min_spend' => 25.00,
                'max_discount' => 15.00,
                'usage_limit' => 500,
                'usage_count' => 84,
                'per_user_limit' => 1,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME20',
                'description' => '$20 off on your first grocery delivery over $60.',
                'type' => Coupon::TYPE_FIXED,
                'value' => 20.00,
                'min_spend' => 60.00,
                'max_discount' => null,
                'usage_limit' => 200,
                'usage_count' => 128,
                'per_user_limit' => 1,
                'starts_at' => now()->subMonths(2),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'ORGANIC15',
                'description' => '15% discount on organic valley and certified organic items.',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 15.00,
                'min_spend' => 30.00,
                'max_discount' => 25.00,
                'usage_limit' => 300,
                'usage_count' => 45,
                'per_user_limit' => 2,
                'starts_at' => now()->subDays(10),
                'expires_at' => now()->addDays(20),
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED50',
                'description' => 'Past promotional seasonal flash voucher.',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 50.00,
                'min_spend' => 100.00,
                'max_discount' => 50.00,
                'usage_limit' => 50,
                'usage_count' => 50,
                'per_user_limit' => 1,
                'starts_at' => now()->subMonths(3),
                'expires_at' => now()->subMonth(),
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
