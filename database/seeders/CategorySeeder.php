<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fresh Fruits & Vegetables',
                'description' => 'Farm fresh organic fruits, crisp vegetables, and fresh herbs delivered daily.',
                'icon' => 'apple',
                'sort_order' => 1,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Fresh Fruits', 'description' => 'Apples, citrus, berries, and exotic tropical fruits.'],
                    ['name' => 'Fresh Vegetables', 'description' => 'Leafy greens, roots, tubers, and gourmet veggies.'],
                    ['name' => 'Organic Herbs', 'description' => 'Fresh basil, rosemary, coriander, and mint.'],
                ]
            ],
            [
                'name' => 'Dairy, Eggs & Cheese',
                'description' => 'Farm fresh whole milk, artisanal cheeses, butter, and free-range eggs.',
                'icon' => 'milk',
                'sort_order' => 2,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Milk & Cream', 'description' => 'Whole milk, almond milk, oat milk, and heavy cream.'],
                    ['name' => 'Artisanal Cheeses', 'description' => 'Cheddar, mozzarella, gouda, and parmesan blocks.'],
                    ['name' => 'Butter & Eggs', 'description' => 'Cultured butter and farm fresh pasture-raised eggs.'],
                    ['name' => 'Yogurt & Cultured', 'description' => 'Greek yogurt, kefir, and probiotic smoothies.'],
                ]
            ],
            [
                'name' => 'Bakery & Bread',
                'description' => 'Freshly baked artisan sourdough, whole grain loaves, and French pastries.',
                'icon' => 'wheat',
                'sort_order' => 3,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Artisan Bread', 'description' => 'Fresh sourdough, baguettes, and multigrain loaves.'],
                    ['name' => 'Pastries & Croissants', 'description' => 'Butter croissants, muffins, and danishes.'],
                    ['name' => 'Buns & Rolls', 'description' => 'Brioche burger buns and dinner rolls.'],
                ]
            ],
            [
                'name' => 'Beverages & Juices',
                'description' => 'Cold-pressed juices, sparkling water, craft sodas, and artisan roasted coffee.',
                'icon' => 'coffee',
                'sort_order' => 4,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Cold-Pressed Juices', 'description' => 'Pure orange, green detox, and berry blends.'],
                    ['name' => 'Artisan Coffee & Tea', 'description' => 'Whole bean roasts and organic herbal teas.'],
                    ['name' => 'Sparkling & Mineral Water', 'description' => 'Natural springs and flavored sparkling waters.'],
                ]
            ],
            [
                'name' => 'Meat & Seafood',
                'description' => 'Grass-fed beef, organic free-range poultry, and wild-caught seafood.',
                'icon' => 'beef',
                'sort_order' => 5,
                'is_featured' => false,
                'children' => [
                    ['name' => 'Poultry', 'description' => 'Organic chicken breasts, wings, and whole turkeys.'],
                    ['name' => 'Beef & Lamb', 'description' => 'Prime cut ribeyes, minced beef, and lamb chops.'],
                    ['name' => 'Wild Seafood', 'description' => 'Atlantic salmon, tiger prawns, and fresh cod fillets.'],
                ]
            ],
            [
                'name' => 'Pantry & Staples',
                'description' => 'Extra virgin olive oils, gourmet pastas, grains, spices, and baking essentials.',
                'icon' => 'package',
                'sort_order' => 6,
                'is_featured' => false,
                'children' => [
                    ['name' => 'Oils & Vinegars', 'description' => 'Cold pressed olive oil and aged balsamic vinegars.'],
                    ['name' => 'Pasta & Rice', 'description' => 'Bronze-cut Italian pasta and aged basmati rice.'],
                    ['name' => 'Spices & Seasonings', 'description' => 'Sea salts, peppercorns, and organic spice blends.'],
                ]
            ],
            [
                'name' => 'Snacks & Confectionery',
                'description' => 'Roasted nuts, healthy trail mixes, artisan dark chocolate, and organic crisps.',
                'icon' => 'cookie',
                'sort_order' => 7,
                'is_featured' => false,
                'children' => [
                    ['name' => 'Nuts & Seeds', 'description' => 'Roasted almonds, cashews, and chia seeds.'],
                    ['name' => 'Dark Chocolates', 'description' => 'Single origin chocolate bars and truffles.'],
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $catData['slug'] = Str::slug($catData['name']);
            $parent = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            foreach ($children as $subIndex => $subData) {
                $subSlug = Str::slug($subData['name']);
                Category::updateOrCreate(
                    ['slug' => $subSlug],
                    [
                        'parent_id' => $parent->id,
                        'name' => $subData['name'],
                        'slug' => $subSlug,
                        'description' => $subData['description'] ?? null,
                        'icon' => $parent->icon,
                        'sort_order' => $subIndex + 1,
                        'is_active' => true,
                        'is_featured' => false,
                    ]
                );
            }
        }
    }
}
