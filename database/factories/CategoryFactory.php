<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Fresh Fruits & Vegetables',
            'Dairy, Eggs & Cheese',
            'Bakery & Bread',
            'Beverages & Juices',
            'Meat & Seafood',
            'Pantry & Staples',
            'Snacks & Confectionery',
            'Organic & Health Food',
            'Frozen Foods',
            'Canned Goods',
        ]);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(1),
            'image' => null,
            'icon' => 'shopping-bag',
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
            'is_featured' => fake()->boolean(40),
        ];
    }
}
