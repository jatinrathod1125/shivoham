<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(4, true),
            'subtitle' => fake()->sentence(),
            'image' => '/images/banners/mock-hero.jpg',
            'link' => '/categories/fresh-produce',
            'position' => fake()->randomElement([
                Banner::POSITION_HOME_HERO,
                Banner::POSITION_POPUP,
                Banner::POSITION_SIDEBAR,
                Banner::POSITION_CATEGORY_TOP,
                Banner::POSITION_PROMOTIONAL_BAR,
            ]),
            'sort_order' => fake()->numberBetween(0, 5),
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(60),
        ];
    }
}
