<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Test guest cannot view order details or invoice.
     */
    public function test_guest_cannot_view_order_details(): void
    {
        $order = Order::factory()->create();

        $this->get("/admin/orders/{$order->id}")->assertRedirect('/admin/login');
        $this->get("/admin/orders/{$order->id}/invoice")->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view order details with line items.
     */
    public function test_admin_can_view_order_details(): void
    {
        $customer = Customer::factory()->create(['name' => 'Sophia Martinez']);
        $product = Product::factory()->create(['name' => 'Organic Fresh Strawberries']);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_name' => 'Sophia Martinez',
            'order_number' => 'ORD-1092',
            'subtotal' => 9.98,
            'tax' => 0.80,
            'shipping_fee' => 4.99,
            'total' => 15.77,
            'status' => Order::STATUS_PROCESSING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Organic Fresh Strawberries',
            'unit_price' => 4.99,
            'quantity' => 2,
            'total' => 9.98,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('Order #ORD-1092');
        $response->assertSee('Sophia Martinez');
        $response->assertSee('Organic Fresh Strawberries');
        $response->assertSee('15.77');
        $response->assertSee('Print Invoice');
    }

    /**
     * Test admin can view printable invoice.
     */
    public function test_admin_can_view_printable_invoice(): void
    {
        $product = Product::factory()->create(['name' => 'Whole Milk 1 Gallon']);

        $order = Order::factory()->create([
            'order_number' => 'ORD-INV-99',
            'total' => 24.50,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Whole Milk 1 Gallon',
            'unit_price' => 4.50,
            'quantity' => 2,
            'total' => 9.00,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}/invoice");

        $response->assertStatus(200);
        $response->assertSee('INVOICE');
        $response->assertSee('#ORD-INV-99');
        $response->assertSee('Whole Milk 1 Gallon');
    }
}
