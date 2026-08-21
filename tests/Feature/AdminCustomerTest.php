<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
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
     * Test guest cannot access customers index.
     */
    public function test_guest_cannot_access_customers(): void
    {
        $response = $this->get('/admin/customers');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view customers index with KPIs.
     */
    public function test_admin_can_view_customers_index(): void
    {
        Customer::factory()->create([
            'name' => 'Eleanor Vance',
            'email' => 'eleanor.vance@example.com',
            'total_spent' => 245.50,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/customers');

        $response->assertStatus(200);
        $response->assertSee('Customers');
        $response->assertSee('Eleanor Vance');
        $response->assertSee('Total Customers');
        $response->assertSee('Customer Revenue');
    }

    /**
     * Test admin can create customer with default delivery address.
     */
    public function test_admin_can_create_customer_with_address(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/customers', [
            'name' => 'Marcus Holloway',
            'email' => 'marcus.h@example.com',
            'phone' => '+1 (555) 789-0123',
            'status' => 'active',
            'address_line1' => '500 Market Street',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'country' => 'US',
        ]);

        $customer = Customer::where('email', 'marcus.h@example.com')->first();
        $this->assertNotNull($customer);
        $response->assertRedirect("/admin/customers/{$customer->id}");

        $this->assertDatabaseHas('customers', [
            'name' => 'Marcus Holloway',
            'email' => 'marcus.h@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'address_line1' => '500 Market Street',
            'city' => 'San Francisco',
            'is_default' => true,
        ]);
    }

    /**
     * Test admin can view customer profile and orders.
     */
    public function test_admin_can_view_customer_profile(): void
    {
        $customer = Customer::factory()->create(['name' => 'Clara Oswald']);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-CLARA-01',
            'total' => 74.90,
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertSee('Clara Oswald');
        $response->assertSee('ORD-CLARA-01');
        $response->assertSee('Lifetime Spend');
    }

    /**
     * Test admin can update customer and default address.
     */
    public function test_admin_can_update_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);
        CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_line1' => '100 Old St',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/customers/{$customer->id}", [
            'name' => 'Updated Customer Name',
            'email' => $customer->email,
            'phone' => '+1 (555) 999-8888',
            'status' => 'active',
            'address_line1' => '200 New Blvd',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
            'country' => 'US',
        ]);

        $response->assertRedirect("/admin/customers/{$customer->id}");

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'address_line1' => '200 New Blvd',
            'city' => 'Chicago',
        ]);
    }

    /**
     * Test quick AJAX status toggle.
     */
    public function test_admin_can_toggle_customer_status_via_ajax(): void
    {
        $customer = Customer::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->postJson("/admin/customers/{$customer->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertEquals('inactive', $customer->fresh()->status);
    }

    /**
     * Test customer with orders cannot be deleted.
     */
    public function test_customer_with_orders_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        Order::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/admin/customers/{$customer->id}");

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    /**
     * Test clean customer without orders can be deleted.
     */
    public function test_clean_customer_can_be_deleted(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/customers/{$customer->id}");

        $response->assertRedirect('/admin/customers');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
