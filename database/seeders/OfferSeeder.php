<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Fresh Produce Seasonal Boost',
                'description' => 'Save up to 20% on all seasonal organic strawberries, apples, and crisp citrus.',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 20.00,
                'badge_text' => 'Campaign Active',
                'starts_at' => now()->subDays(5),
                'expires_at' => now()->addDays(25),
                'is_active' => true,
            ],
            [
                'title' => 'Artisanal Dairy & Cheese Festival',
                'description' => 'Buy organic whole milk & Kerrygold grass-fed butter with 15% instant savings.',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 15.00,
                'badge_text' => 'Weekend Special',
                'starts_at' => now()->subDays(2),
                'expires_at' => now()->addDays(12),
                'is_active' => true,
            ],
            [
                'title' => 'Morning Bakery Sourdough Delight',
                'description' => 'Pair freshly baked artisan loaves with French butter croissants for special $5 combo.',
                'discount_type' => Offer::TYPE_FIXED,
                'discount_value' => 5.00,
                'badge_text' => 'Hot Deal',
                'starts_at' => now()->subDays(1),
                'expires_at' => now()->addDays(14),
                'is_active' => true,
            ],
        ];

        foreach ($offers as $offer) {
            $offer['slug'] = Str::slug($offer['title']);
            Offer::updateOrCreate(
                ['slug' => $offer['slug']],
                $offer
            );
        }
    }
}
