<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
            'website' => fake()->url(),
            'description' => fake()->paragraph(1),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
        ];
    }
}
