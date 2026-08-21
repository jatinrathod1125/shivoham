<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Organic Valley', 'description' => 'Farmer-owned cooperative producing pure organic dairy and produce.', 'is_featured' => true],
            ['name' => 'Kerrygold', 'description' => 'Rich grass-fed Irish butter and premium aged cheeses.', 'is_featured' => true],
            ['name' => "Bob's Red Mill", 'description' => 'Wholesome stone-ground whole grains, flours, and cereals.', 'is_featured' => true],
            ['name' => 'Chobani', 'description' => 'Greek yogurts crafted with natural ingredients and live cultures.', 'is_featured' => true],
            ['name' => 'Oatly', 'description' => 'Original Swedish oat-based dairy alternatives and barista milks.', 'is_featured' => true],
            ['name' => 'Horizon Organic', 'description' => 'Certified organic milk, eggs, and dairy pantry items.', 'is_featured' => false],
            ['name' => 'Barilla', 'description' => 'Master pasta makers since 1877 from Parma, Italy.', 'is_featured' => false],
            ['name' => 'San Pellegrino', 'description' => 'Italian natural mineral water and sparkling fruit beverages.', 'is_featured' => false],
            ['name' => "Nature's Bakery", 'description' => 'Whole wheat fig bars and gluten-free snack bites.', 'is_featured' => false],
            ['name' => 'Driscoll’s', 'description' => 'Only the finest fresh strawberries, blueberries, and raspberries.', 'is_featured' => true],
        ];

        foreach ($brands as $brand) {
            $brand['slug'] = Str::slug($brand['name']);
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }
    }
}
