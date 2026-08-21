<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 250);
        $tax = round($subtotal * 0.08, 2);
        $shipping = fake()->randomElement([0.00, 4.99, 9.99]);
        $discount = fake()->optional(0.3, 0.00)->randomFloat(2, 5, 20);
        $total = round($subtotal + $tax + $shipping - $discount, 2);

        $status = fake()->randomElement([
            Order::STATUS_DELIVERED,
            Order::STATUS_DELIVERED,
            Order::STATUS_PROCESSING,
            Order::STATUS_PENDING,
            Order::STATUS_CANCELLED,
        ]);

        $paymentStatus = match ($status) {
            Order::STATUS_DELIVERED, Order::STATUS_PROCESSING => Order::PAYMENT_PAID,
            Order::STATUS_CANCELLED => fake()->randomElement([Order::PAYMENT_UNPAID, Order::PAYMENT_REFUNDED]),
            default => fake()->randomElement([Order::PAYMENT_UNPAID, Order::PAYMENT_PAID]),
        };

        return [
            'order_number' => 'ORD-' . fake()->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'billing_address' => fake()->address(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon_code' => $discount > 0 ? 'SAVE' . fake()->numberBetween(10, 20) : null,
            'tax' => $tax,
            'shipping_fee' => $shipping,
            'total' => $total,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => fake()->randomElement(['cash_on_delivery', 'card', 'stripe', 'apple_pay']),
            'notes' => fake()->optional(0.2)->sentence(),
            'delivered_at' => $status === Order::STATUS_DELIVERED ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
