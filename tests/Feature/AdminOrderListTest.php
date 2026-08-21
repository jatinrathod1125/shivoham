<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderListTest extends TestCase
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
     * Test guest cannot access orders index.
     */
    public function test_guest_cannot_access_orders(): void
    {
        $response = $this->get('/admin/orders');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view orders index with KPIs and status navigation tabs.
     */
    public function test_admin_can_view_orders_index(): void
    {
        $customer = Customer::factory()->create(['name' => 'Sophia Martinez']);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_name' => 'Sophia Martinez',
            'order_number' => 'ORD-9842',
            'status' => Order::STATUS_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
            'total' => 68.40,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('Orders');
        $response->assertSee('ORD-9842');
        $response->assertSee('Sophia Martinez');
        $response->assertSee('Total Orders');
        $response->assertSee('Paid Revenue');
    }

    /**
     * Test search filter by order number and customer name.
     */
    public function test_admin_can_search_orders(): void
    {
        Order::factory()->create([
            'order_number' => 'ORD-ALPHA-100',
            'customer_name' => 'John Doe',
        ]);

        Order::factory()->create([
            'order_number' => 'ORD-BETA-200',
            'customer_name' => 'Jane Smith',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders?search=ORD-ALPHA-100');
        $response->assertSee('ORD-ALPHA-100');
        $response->assertDontSee('ORD-BETA-200');
    }

    /**
     * Test status tab filtering.
     */
    public function test_status_tab_filtering(): void
    {
        Order::factory()->create([
            'order_number' => 'ORD-PEND-01',
            'status' => Order::STATUS_PENDING,
        ]);

        Order::factory()->create([
            'order_number' => 'ORD-DELV-02',
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders?status=pending');
        $response->assertSee('ORD-PEND-01');
        $response->assertDontSee('ORD-DELV-02');
    }

    /**
     * Test quick AJAX status update.
     */
    public function test_admin_can_update_order_status_via_ajax(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->actingAs($this->admin)->postJson("/admin/orders/{$order->id}/update-status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'delivered']);
        $this->assertEquals(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
    }

    /**
     * Test quick AJAX payment status update.
     */
    public function test_admin_can_update_payment_status_via_ajax(): void
    {
        $order = Order::factory()->create(['payment_status' => Order::PAYMENT_UNPAID]);

        $response = $this->actingAs($this->admin)->postJson("/admin/orders/{$order->id}/update-payment-status", [
            'payment_status' => 'paid',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'payment_status' => 'paid']);
        $this->assertEquals(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }
}
