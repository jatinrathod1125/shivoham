<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
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
     * Test guest cannot access reports.
     */
    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view reports dashboard with KPIs and chart datasets.
     */
    public function test_admin_can_view_reports_index(): void
    {
        $category = Category::factory()->create(['name' => 'Fresh Fruits']);
        $product = Product::factory()->create([
            'name' => 'Honeycrisp Apples',
            'category_id' => $category->id,
            'selling_price' => 5.00,
            'cost_price' => 2.50,
        ]);

        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_PAID,
            'total' => 50.00,
            'created_at' => now()->subDays(2),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 5.00,
            'quantity' => 10,
            'total' => 50.00,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reports?range=30_days');

        $response->assertStatus(200);
        $response->assertSee('Analytics &amp; Sales Reports', false);
        $response->assertSee('Gross Revenue');
        $response->assertSee('Completed Orders');
        $response->assertSee('Average Order Value');
        $response->assertSee('Honeycrisp Apples');
        $response->assertSee('Export CSV');
    }

    /**
     * Test date range filter switches properly.
     */
    public function test_admin_can_filter_by_date_range(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports?range=7_days');
        $response->assertStatus(200);
        $response->assertSee('Last 7 Days');
    }

    /**
     * Test admin can download CSV sales report.
     */
    public function test_admin_can_export_csv_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports/export?range=30_days');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test admin can view inventory velocity and stock health report.
     */
    public function test_admin_can_view_inventory_velocity_report(): void
    {
        $product = Product::factory()->create([
            'name' => 'Organic Fresh Milk',
            'stock_quantity' => 15,
            'min_stock_threshold' => 20,
            'selling_price' => 4.50,
            'cost_price' => 2.00,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reports/inventory');

        $response->assertStatus(200);
        $response->assertSee('Inventory Velocity');
        $response->assertSee('Catalog Valuation');
        $response->assertSee('Low Stock Warnings');
        $response->assertSee('Organic Fresh Milk');
    }

    /**
     * Test admin can export inventory velocity report CSV.
     */
    public function test_admin_can_export_inventory_csv_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports/inventory/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
