<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Fresh Organic Harvest Delivered Same-Day',
                'subtitle' => 'Farm-to-door fresh produce, artisanal dairy, and pantry essentials in under 2 hours.',
                'image' => '/images/banners/hero-grocery-1.jpg',
                'link' => '/admin/products',
                'position' => Banner::POSITION_HOME_HERO,
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(6),
            ],
            [
                'title' => 'Weekend Fresh Fruit Festival — 25% Off',
                'subtitle' => 'Handpicked berries, crisp apples, and tropical delights.',
                'image' => '/images/banners/hero-grocery-2.jpg',
                'link' => '/admin/offers',
                'position' => Banner::POSITION_PROMOTIONAL_BAR,
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'expires_at' => now()->addDays(10),
            ],
            [
                'title' => 'Artisanal Dairy & Grass-Fed Irish Butter',
                'subtitle' => 'Explore Kerrygold, Organic Valley & Chobani specialty collections.',
                'image' => '/images/banners/banner-dairy.jpg',
                'link' => '/admin/categories',
                'position' => Banner::POSITION_CATEGORY_TOP,
                'sort_order' => 3,
                'is_active' => true,
                'starts_at' => now()->subDays(15),
                'expires_at' => now()->addMonths(3),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['title' => $banner['title'], 'position' => $banner['position']],
                $banner
            );
        }
    }
}
