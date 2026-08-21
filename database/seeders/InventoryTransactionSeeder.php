<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $products = Product::all();

        foreach ($products as $product) {
            // Seed initial intake
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $admin?->id,
                'type' => InventoryTransaction::TYPE_ADDITION,
                'quantity' => $product->stock_quantity + 20,
                'previous_stock' => 0,
                'current_stock' => $product->stock_quantity + 20,
                'reason' => 'Initial inventory batch intake from primary supplier',
                'reference_id' => 'PO-' . fake()->numerify('#####'),
                'created_at' => now()->subWeeks(2),
            ]);

            // Seed order deduction
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => null,
                'type' => InventoryTransaction::TYPE_ORDER,
                'quantity' => 20,
                'previous_stock' => $product->stock_quantity + 20,
                'current_stock' => $product->stock_quantity,
                'reason' => 'Online customer sales order fulfillment',
                'reference_id' => 'ORD-' . fake()->numberBetween(1000, 1080),
                'created_at' => now()->subDays(3),
            ]);
        }
    }
}
