<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Test authenticated admin can render dashboard successfully.
     */
    public function test_authenticated_admin_can_render_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Store Admin',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Store Admin');
        $response->assertSee("grocery store today");
        $response->assertSee('Total Orders');
        $response->assertSee('Total Sales');
        $response->assertSee('New Customers');
        $response->assertSee('Low Stock Items');
        $response->assertSee('Sales Overview');
        $response->assertSee('Order Status Breakdown');
        $response->assertSee('Quick Actions');
        $response->assertSee('Recent Orders');
        $response->assertSee('Top Categories');
        $response->assertViewHasAll([
            'stats',
            'salesChartData',
            'orderStatusBreakdown',
            'recentOrders',
            'topCategories',
        ]);
    }
}
