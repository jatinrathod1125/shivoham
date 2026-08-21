<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductListTest extends TestCase
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
     * Test guest cannot access products index.
     */
    public function test_guest_cannot_access_products(): void
    {
        $response = $this->get('/admin/products');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view products index.
     */
    public function test_admin_can_view_products_index(): void
    {
        $category = Category::factory()->create(['name' => 'Fresh Fruits']);
        $brand = Brand::factory()->create(['name' => 'Driscoll\'s']);

        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Organic Fresh Strawberries',
            'sku' => 'PRD-STRW-400',
            'selling_price' => 4.99,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Products');
        $response->assertSee('Organic Fresh Strawberries');
        $response->assertSee('PRD-STRW-400');
        $response->assertSee('Total Products');
    }

    /**
     * Test search filter works across name, SKU, and barcode.
     */
    public function test_admin_can_search_products(): void
    {
        Product::factory()->create([
            'name' => 'Golden Delicious Apples',
            'sku' => 'PRD-AAPL-001',
            'barcode' => '8901234567890',
        ]);

        Product::factory()->create([
            'name' => 'Whole Wheat Bread',
            'sku' => 'PRD-BRD-002',
            'barcode' => '8909876543210',
        ]);

        // Search by SKU
        $responseSku = $this->actingAs($this->admin)->get('/admin/products?search=PRD-AAPL-001');
        $responseSku->assertSee('Golden Delicious Apples');
        $responseSku->assertDontSee('Whole Wheat Bread');

        // Search by barcode
        $responseBarcode = $this->actingAs($this->admin)->get('/admin/products?search=8909876543210');
        $responseBarcode->assertSee('Whole Wheat Bread');
        $responseBarcode->assertDontSee('Golden Delicious Apples');
    }

    /**
     * Test stock status filter works.
     */
    public function test_stock_status_filter(): void
    {
        Product::factory()->create([
            'name' => 'In Stock Item',
            'stock_quantity' => 50,
            'min_stock_threshold' => 10,
        ]);

        Product::factory()->create([
            'name' => 'Low Stock Item',
            'stock_quantity' => 3,
            'min_stock_threshold' => 10,
        ]);

        Product::factory()->create([
            'name' => 'Out of Stock Item',
            'stock_quantity' => 0,
            'min_stock_threshold' => 10,
        ]);

        $responseLow = $this->actingAs($this->admin)->get('/admin/products?stock_status=low_stock');
        $responseLow->assertSee('Low Stock Item');
        $responseLow->assertDontSee('In Stock Item');
        $responseLow->assertDontSee('Out of Stock Item');
    }

    /**
     * Test quick AJAX status toggle.
     */
    public function test_admin_can_toggle_product_status_via_ajax(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/products/{$product->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($product->fresh()->is_active);
    }

    /**
     * Test quick AJAX featured toggle.
     */
    public function test_admin_can_toggle_product_featured_via_ajax(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)->postJson("/admin/products/{$product->id}/toggle-featured");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_featured' => true]);
        $this->assertTrue($product->fresh()->is_featured);
    }

    /**
     * Test quick stock adjustment creates inventory audit transaction.
     */
    public function test_admin_can_quick_adjust_stock_with_inventory_log(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->actingAs($this->admin)->postJson("/admin/products/{$product->id}/quick-stock", [
            'adjustment_type' => 'add',
            'quantity' => 25,
            'reason' => 'Emergency batch delivery',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'new_stock' => 35]);
        $this->assertEquals(35, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 25,
            'previous_stock' => 10,
            'current_stock' => 35,
            'reason' => 'Emergency batch delivery',
        ]);
    }

    /**
     * Test product with order items cannot be deleted.
     */
    public function test_product_with_orders_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/admin/products/{$product->id}");

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /**
     * Test safe product deletion when no orders are attached.
     */
    public function test_product_without_orders_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
