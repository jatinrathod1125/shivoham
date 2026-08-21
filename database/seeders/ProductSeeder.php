<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $driscolls = Brand::where('slug', 'driscolls')->first();
        $organicValley = Brand::where('slug', 'organic-valley')->first();
        $kerrygold = Brand::where('slug', 'kerrygold')->first();
        $bobs = Brand::where('slug', 'bobs-red-mill')->first();
        $chobani = Brand::where('slug', 'chobani')->first();
        $oatly = Brand::where('slug', 'oatly')->first();
        $barilla = Brand::where('slug', 'barilla')->first();
        $sanPellegrino = Brand::where('slug', 'san-pellegrino')->first();

        $fruitsCat = Category::where('slug', 'fresh-fruits')->first();
        $vegCat = Category::where('slug', 'fresh-vegetables')->first();
        $herbsCat = Category::where('slug', 'organic-herbs')->first();
        $milkCat = Category::where('slug', 'milk-cream')->first();
        $cheeseCat = Category::where('slug', 'artisanal-cheeses')->first();
        $butterCat = Category::where('slug', 'butter-eggs')->first();
        $yogurtCat = Category::where('slug', 'yogurt-cultured')->first();
        $breadCat = Category::where('slug', 'artisan-bread')->first();
        $pastryCat = Category::where('slug', 'pastries-croissants')->first();
        $juiceCat = Category::where('slug', 'cold-pressed-juices')->first();
        $waterCat = Category::where('slug', 'sparkling-mineral-water')->first();
        $pastaCat = Category::where('slug', 'pasta-rice')->first();
        $oilsCat = Category::where('slug', 'oils-vinegars')->first();

        $products = [
            // Fruits & Vegetables
            [
                'name' => 'Organic Fresh Strawberries 400g',
                'category_id' => $fruitsCat?->id,
                'brand_id' => $driscolls?->id,
                'sku' => 'PRD-STRW-400',
                'cost_price' => 2.80,
                'selling_price' => 4.99,
                'special_price' => 3.99,
                'stock_quantity' => 48,
                'min_stock_threshold' => 15,
                'unit' => 'pack',
                'weight' => 0.40,
                'is_featured' => true,
                'short_description' => 'Sweet, fragrant certified organic strawberries harvested at peak ripeness.',
            ],
            [
                'name' => 'Organic Crisp Honeycrisp Apples 1kg',
                'category_id' => $fruitsCat?->id,
                'brand_id' => $organicValley?->id,
                'sku' => 'PRD-APPL-1KG',
                'cost_price' => 3.20,
                'selling_price' => 5.49,
                'special_price' => null,
                'stock_quantity' => 65,
                'min_stock_threshold' => 20,
                'unit' => 'kg',
                'weight' => 1.00,
                'is_featured' => true,
                'short_description' => 'Crisp, sweet, and juicy Honeycrisp apples perfect for snacking and baking.',
            ],
            [
                'name' => 'Organic Hass Avocados (Pack of 4)',
                'category_id' => $vegCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-AVOC-004',
                'cost_price' => 3.00,
                'selling_price' => 5.99,
                'special_price' => 4.99,
                'stock_quantity' => 35,
                'min_stock_threshold' => 12,
                'unit' => 'pack',
                'weight' => 0.60,
                'is_featured' => true,
                'short_description' => 'Creamy, rich Hass avocados ideal for toast, salads, and fresh guacamole.',
            ],
            [
                'name' => 'Fresh Baby Spinach 250g',
                'category_id' => $vegCat?->id,
                'brand_id' => $organicValley?->id,
                'sku' => 'PRD-SPIN-250',
                'cost_price' => 1.40,
                'selling_price' => 2.99,
                'special_price' => null,
                'stock_quantity' => 22,
                'min_stock_threshold' => 10,
                'unit' => 'pack',
                'weight' => 0.25,
                'is_featured' => false,
                'short_description' => 'Tender pre-washed organic baby spinach leaves packed with iron and nutrients.',
            ],
            [
                'name' => 'Organic Fresh Sweet Basil Bunch',
                'category_id' => $herbsCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-BASL-001',
                'cost_price' => 0.80,
                'selling_price' => 1.99,
                'special_price' => null,
                'stock_quantity' => 8, // Low stock!
                'min_stock_threshold' => 10,
                'unit' => 'bunch',
                'weight' => 0.05,
                'is_featured' => false,
                'short_description' => 'Aromatic Genovese sweet basil with vibrant green leaves for pasta and pesto.',
            ],

            // Dairy & Eggs
            [
                'name' => 'Organic Pasture-Raised Whole Milk 1L',
                'category_id' => $milkCat?->id,
                'brand_id' => $organicValley?->id,
                'sku' => 'PRD-MILK-1000',
                'cost_price' => 2.10,
                'selling_price' => 3.89,
                'special_price' => null,
                'stock_quantity' => 54,
                'min_stock_threshold' => 15,
                'unit' => 'liter',
                'weight' => 1.00,
                'is_featured' => true,
                'short_description' => 'Rich, creamy certified organic whole milk from pasture-raised happy cows.',
            ],
            [
                'name' => 'Oatly Barista Edition Oat Milk 1L',
                'category_id' => $milkCat?->id,
                'brand_id' => $oatly?->id,
                'sku' => 'PRD-OATLY-BAR',
                'cost_price' => 2.50,
                'selling_price' => 4.49,
                'special_price' => 3.99,
                'stock_quantity' => 40,
                'min_stock_threshold' => 12,
                'unit' => 'liter',
                'weight' => 1.00,
                'is_featured' => true,
                'short_description' => 'Micro-foamable plant-based barista oat milk designed for specialty coffees.',
            ],
            [
                'name' => 'Kerrygold Pure Irish Salted Butter 250g',
                'category_id' => $butterCat?->id,
                'brand_id' => $kerrygold?->id,
                'sku' => 'PRD-BUTR-250',
                'cost_price' => 2.90,
                'selling_price' => 4.99,
                'special_price' => null,
                'stock_quantity' => 6, // Low stock!
                'min_stock_threshold' => 10,
                'unit' => 'piece',
                'weight' => 0.25,
                'is_featured' => true,
                'short_description' => 'Creamy Irish butter produced from the milk of grass-fed cows.',
            ],
            [
                'name' => 'Pasture-Raised Grade A Large Eggs (12pk)',
                'category_id' => $butterCat?->id,
                'brand_id' => $organicValley?->id,
                'sku' => 'PRD-EGGS-012',
                'cost_price' => 3.50,
                'selling_price' => 5.99,
                'special_price' => null,
                'stock_quantity' => 30,
                'min_stock_threshold' => 10,
                'unit' => 'pack',
                'weight' => 0.70,
                'is_featured' => true,
                'short_description' => 'Farm fresh large brown eggs with deep golden yolks from roaming hens.',
            ],
            [
                'name' => 'Chobani Plain Non-Fat Greek Yogurt 900g',
                'category_id' => $yogurtCat?->id,
                'brand_id' => $chobani?->id,
                'sku' => 'PRD-YOGT-900',
                'cost_price' => 3.80,
                'selling_price' => 6.29,
                'special_price' => 5.49,
                'stock_quantity' => 18,
                'min_stock_threshold' => 8,
                'unit' => 'piece',
                'weight' => 0.90,
                'is_featured' => false,
                'short_description' => 'High protein, triple strained authentic plain Greek yogurt with live active cultures.',
            ],
            [
                'name' => 'Kerrygold Aged Reserve Cheddar 200g',
                'category_id' => $cheeseCat?->id,
                'brand_id' => $kerrygold?->id,
                'sku' => 'PRD-CHED-200',
                'cost_price' => 3.40,
                'selling_price' => 5.79,
                'special_price' => null,
                'stock_quantity' => 14,
                'min_stock_threshold' => 6,
                'unit' => 'piece',
                'weight' => 0.20,
                'is_featured' => false,
                'short_description' => 'Sharp, full-bodied Irish cheddar naturally aged over 2 years.',
            ],

            // Bakery
            [
                'name' => 'Artisan Rustic Sourdough Loaf 600g',
                'category_id' => $breadCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-SOUR-600',
                'cost_price' => 2.20,
                'selling_price' => 4.50,
                'special_price' => null,
                'stock_quantity' => 15,
                'min_stock_threshold' => 8,
                'unit' => 'piece',
                'weight' => 0.60,
                'is_featured' => true,
                'short_description' => 'Slow-fermented artisan sourdough with a blistered crunchy crust and soft crumb.',
            ],
            [
                'name' => 'French Pure Butter Croissants (Pack of 4)',
                'category_id' => $pastryCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-CRSN-004',
                'cost_price' => 2.90,
                'selling_price' => 5.25,
                'special_price' => 4.50,
                'stock_quantity' => 4, // Low stock!
                'min_stock_threshold' => 8,
                'unit' => 'pack',
                'weight' => 0.32,
                'is_featured' => true,
                'short_description' => 'Flaky, buttery multi-layered croissants baked fresh every morning.',
            ],

            // Beverages
            [
                'name' => 'Cold-Pressed Valencia Orange Juice 1L',
                'category_id' => $juiceCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-JUIC-1000',
                'cost_price' => 3.10,
                'selling_price' => 5.99,
                'special_price' => 4.99,
                'stock_quantity' => 28,
                'min_stock_threshold' => 10,
                'unit' => 'liter',
                'weight' => 1.00,
                'is_featured' => true,
                'short_description' => '100% freshly squeezed unpasteurized Valencia oranges with juicy pulp.',
            ],
            [
                'name' => 'San Pellegrino Sparkling Natural Mineral Water 750ml',
                'category_id' => $waterCat?->id,
                'brand_id' => $sanPellegrino?->id,
                'sku' => 'PRD-SPEL-750',
                'cost_price' => 1.20,
                'selling_price' => 2.79,
                'special_price' => null,
                'stock_quantity' => 72,
                'min_stock_threshold' => 24,
                'unit' => 'bottle',
                'weight' => 0.75,
                'is_featured' => false,
                'short_description' => 'Iconic Italian mineral water with fine, persistent bubbles and crisp mineral taste.',
            ],

            // Pantry & Grains
            [
                'name' => "Bob's Red Mill Organic Rolled Oats 907g",
                'category_id' => $pastaCat?->id,
                'brand_id' => $bobs?->id,
                'sku' => 'PRD-OATS-907',
                'cost_price' => 3.20,
                'selling_price' => 5.49,
                'special_price' => null,
                'stock_quantity' => 38,
                'min_stock_threshold' => 10,
                'unit' => 'pack',
                'weight' => 0.907,
                'is_featured' => false,
                'short_description' => 'Old fashioned whole grain organic rolled oats rich in soluble fiber.',
            ],
            [
                'name' => 'Barilla Collezione Bronze-Cut Spaghetti 500g',
                'category_id' => $pastaCat?->id,
                'brand_id' => $barilla?->id,
                'sku' => 'PRD-SPAG-500',
                'cost_price' => 1.50,
                'selling_price' => 2.99,
                'special_price' => null,
                'stock_quantity' => 55,
                'min_stock_threshold' => 15,
                'unit' => 'pack',
                'weight' => 0.50,
                'is_featured' => false,
                'short_description' => 'Premium bronze-die extruded spaghetti with rough texture for maximum sauce cling.',
            ],
            [
                'name' => 'Cold-Pressed Extra Virgin Olive Oil 750ml',
                'category_id' => $oilsCat?->id,
                'brand_id' => null,
                'sku' => 'PRD-EVOO-750',
                'cost_price' => 7.50,
                'selling_price' => 13.99,
                'special_price' => 11.99,
                'stock_quantity' => 3, // Low stock!
                'min_stock_threshold' => 8,
                'unit' => 'bottle',
                'weight' => 0.75,
                'is_featured' => true,
                'short_description' => 'First cold pressed unfiltered single-estate olive oil with peppery herbal notes.',
            ],
        ];

        foreach ($products as $pData) {
            $slug = Str::slug($pData['name']);
            Product::updateOrCreate(
                ['sku' => $pData['sku']],
                array_merge($pData, [
                    'slug' => $slug,
                    'barcode' => '890' . fake()->numerify('##########'),
                    'description' => $pData['short_description'] . ' High quality grocery item packaged with eco-friendly sustainable materials.',
                    'is_active' => true,
                ])
            );
        }
    }
}
