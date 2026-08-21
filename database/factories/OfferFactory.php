<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Weekend Fresh Produce Sale',
            'Dairy Week Special 20% Off',
            'Bakery Sunrise Discount',
            'Summer Beverage Fest',
            'Organic Farm Direct Deals',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(10, 999),
            'description' => fake()->paragraph(1),
            'banner_image' => null,
            'discount_type' => Offer::TYPE_PERCENTAGE,
            'discount_value' => fake()->randomElement([15, 20, 25, 30]),
            'badge_text' => fake()->randomElement(['Limited Time', 'Hot Deal', 'Member Special', 'Weekend Only']),
            'starts_at' => now()->subDays(2),
            'expires_at' => now()->addDays(10),
            'is_active' => true,
        ];
    }
}
