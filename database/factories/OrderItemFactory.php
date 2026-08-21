<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 2, 40);
        $quantity = fake()->numberBetween(1, 6);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'sku' => 'SKU-' . strtoupper(fake()->bothify('##??##')),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total' => round($unitPrice * $quantity, 2),
        ];
    }
}
