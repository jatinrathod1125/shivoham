<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            InventoryTransaction::TYPE_ADDITION,
            InventoryTransaction::TYPE_DEDUCTION,
            InventoryTransaction::TYPE_ADJUSTMENT,
            InventoryTransaction::TYPE_ORDER,
        ]);
        $qty = fake()->numberBetween(1, 50);
        $prev = fake()->numberBetween(50, 200);
        $current = $type === InventoryTransaction::TYPE_ADDITION ? $prev + $qty : max(0, $prev - $qty);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'quantity' => $qty,
            'previous_stock' => $prev,
            'current_stock' => $current,
            'reason' => fake()->randomElement(['Supplier restock', 'Online order fulfillment', 'Damaged goods write-off', 'Cycle count discrepancy correction']),
            'reference_id' => 'REF-' . fake()->bothify('###???'),
        ];
    }
}
