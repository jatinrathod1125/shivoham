<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $sarah = Customer::where('email', 'sarah.jenkins@example.com')->first();
        $michael = Customer::where('email', 'michael.chang@example.com')->first();
        $emma = Customer::where('email', 'emma.watson@example.com')->first();
        $david = Customer::where('email', 'david.miller@example.com')->first();
        $olivia = Customer::where('email', 'olivia.rodriguez@example.com')->first();

        $strawberry = Product::where('sku', 'PRD-STRW-400')->first();
        $milk = Product::where('sku', 'PRD-MILK-1000')->first();
        $butter = Product::where('sku', 'PRD-BUTR-250')->first();
        $eggs = Product::where('sku', 'PRD-EGGS-012')->first();
        $sourdough = Product::where('sku', 'PRD-SOUR-600')->first();
        $croissants = Product::where('sku', 'PRD-CRSN-004')->first();
        $orangeJuice = Product::where('sku', 'PRD-JUIC-1000')->first();
        $oliveOil = Product::where('sku', 'PRD-EVOO-750')->first();

        $orders = [
            [
                'order_number' => 'ORD-1092',
                'customer_id' => $sarah?->id,
                'customer_name' => $sarah?->name ?? 'Sarah Jenkins',
                'customer_email' => $sarah?->email ?? 'sarah.jenkins@example.com',
                'customer_phone' => $sarah?->phone ?? '+1 (555) 234-5678',
                'shipping_address' => '742 Evergreen Terrace, Springfield, IL 62704',
                'status' => Order::STATUS_DELIVERED,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'card',
                'notes' => 'Leave at front porch next to door.',
                'delivered_at' => now()->subMinutes(45),
                'created_at' => now()->subHours(2),
                'items' => [
                    ['product' => $strawberry, 'qty' => 2],
                    ['product' => $milk, 'qty' => 2],
                    ['product' => $croissants, 'qty' => 1],
                ]
            ],
            [
                'order_number' => 'ORD-1091',
                'customer_id' => $michael?->id,
                'customer_name' => $michael?->name ?? 'Michael Chang',
                'customer_email' => $michael?->email ?? 'michael.chang@example.com',
                'customer_phone' => $michael?->phone ?? '+1 (555) 345-6789',
                'shipping_address' => '1204 Pine Street, Apt 3B, Seattle, WA 98101',
                'status' => Order::STATUS_PROCESSING,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'apple_pay',
                'notes' => 'Please call upon arrival.',
                'delivered_at' => null,
                'created_at' => now()->subHours(4),
                'items' => [
                    ['product' => $sourdough, 'qty' => 1],
                    ['product' => $butter, 'qty' => 1],
                    ['product' => $eggs, 'qty' => 2],
                    ['product' => $orangeJuice, 'qty' => 1],
                ]
            ],
            [
                'order_number' => 'ORD-1090',
                'customer_id' => $emma?->id,
                'customer_name' => $emma?->name ?? 'Emma Watson',
                'customer_email' => $emma?->email ?? 'emma.watson@example.com',
                'customer_phone' => $emma?->phone ?? '+1 (555) 456-7890',
                'shipping_address' => '456 Oak Avenue, Austin, TX 78701',
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'payment_method' => 'cash_on_delivery',
                'notes' => null,
                'delivered_at' => null,
                'created_at' => now()->subHours(6),
                'items' => [
                    ['product' => $oliveOil, 'qty' => 1],
                    ['product' => $strawberry, 'qty' => 3],
                ]
            ],
            [
                'order_number' => 'ORD-1089',
                'customer_id' => $david?->id,
                'customer_name' => $david?->name ?? 'David Miller',
                'customer_email' => $david?->email ?? 'david.miller@example.com',
                'customer_phone' => $david?->phone ?? '+1 (555) 567-8901',
                'shipping_address' => '89 Market Boulevard, Denver, CO 80202',
                'status' => Order::STATUS_DELIVERED,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'card',
                'notes' => null,
                'delivered_at' => now()->subHours(12),
                'created_at' => now()->subHours(15),
                'items' => [
                    ['product' => $milk, 'qty' => 3],
                    ['product' => $eggs, 'qty' => 1],
                ]
            ],
            [
                'order_number' => 'ORD-1088',
                'customer_id' => $olivia?->id,
                'customer_name' => $olivia?->name ?? 'Olivia Rodriguez',
                'customer_email' => $olivia?->email ?? 'olivia.rodriguez@example.com',
                'customer_phone' => $olivia?->phone ?? '+1 (555) 678-9012',
                'shipping_address' => '321 Ocean Drive, Miami, FL 33139',
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => Order::PAYMENT_REFUNDED,
                'payment_method' => 'card',
                'notes' => 'Customer requested cancellation due to travel.',
                'delivered_at' => null,
                'created_at' => now()->subDay(),
                'items' => [
                    ['product' => $oliveOil, 'qty' => 2],
                    ['product' => $sourdough, 'qty' => 2],
                ]
            ],
        ];

        foreach ($orders as $oData) {
            $items = $oData['items'];
            unset($oData['items']);

            $subtotal = 0;
            $orderItemsToCreate = [];

            foreach ($items as $item) {
                $p = $item['product'];
                if (!$p) continue;
                $price = $p->effective_price;
                $qty = $item['qty'];
                $total = round($price * $qty, 2);
                $subtotal += $total;

                $orderItemsToCreate[] = [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'sku' => $p->sku,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total' => $total,
                ];
            }

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal > 40 ? 0.00 : 4.99;
            $discount = 0.00;
            $total = round($subtotal + $tax + $shipping - $discount, 2);

            $order = Order::updateOrCreate(
                ['order_number' => $oData['order_number']],
                array_merge($oData, [
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping_fee' => $shipping,
                    'discount' => $discount,
                    'total' => $total,
                ])
            );

            foreach ($orderItemsToCreate as $itemData) {
                $order->items()->updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $itemData['product_id']],
                    $itemData
                );
            }
        }
    }
}
