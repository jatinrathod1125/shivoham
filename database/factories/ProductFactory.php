<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $costPrice = fake()->randomFloat(2, 1, 30);
        $sellingPrice = round($costPrice * fake()->randomFloat(2, 1.25, 1.8), 2);
        $hasSpecial = fake()->boolean(25);
        $specialPrice = $hasSpecial ? round($sellingPrice * 0.85, 2) : null;

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 9999),
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'barcode' => fake()->unique()->ean13(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(3),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'special_price' => $specialPrice,
            'special_price_start' => $hasSpecial ? now()->subDays(2) : null,
            'special_price_end' => $hasSpecial ? now()->addDays(14) : null,
            'stock_quantity' => fake()->numberBetween(0, 150),
            'min_stock_threshold' => 10,
            'unit' => fake()->randomElement(['piece', 'kg', 'pack', 'liter', 'bunch', 'bottle', 'can', 'box']),
            'weight' => fake()->randomFloat(2, 0.1, 5.0),
            'thumbnail' => null,
            'images' => [],
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'meta_title' => $name,
            'meta_description' => fake()->sentence(),
        ];
    }
}
