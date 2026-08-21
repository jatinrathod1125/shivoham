<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $type = fake()->randomElement([Coupon::TYPE_PERCENTAGE, Coupon::TYPE_FIXED]);
        $val = $type === Coupon::TYPE_PERCENTAGE ? fake()->randomElement([10, 15, 20, 25]) : fake()->randomElement([5, 10, 15, 20]);

        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'description' => fake()->sentence(),
            'type' => $type,
            'value' => $val,
            'min_spend' => fake()->randomElement([20.00, 30.00, 50.00, null]),
            'max_discount' => $type === Coupon::TYPE_PERCENTAGE ? 25.00 : null,
            'usage_limit' => fake()->randomElement([50, 100, 500, null]),
            'usage_count' => fake()->numberBetween(0, 30),
            'per_user_limit' => 1,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
