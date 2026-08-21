<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'avatar' => null,
            'status' => fake()->randomElement([Customer::STATUS_ACTIVE, Customer::STATUS_ACTIVE, Customer::STATUS_ACTIVE, Customer::STATUS_INACTIVE]),
            'email_verified_at' => now(),
            'total_orders_count' => fake()->numberBetween(1, 20),
            'total_spent' => fake()->randomFloat(2, 50, 2500),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
