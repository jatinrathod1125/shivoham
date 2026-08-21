<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductCreateEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->category = Category::factory()->create(['name' => 'Bakery & Bread']);
        $this->brand = Brand::factory()->create(['name' => 'Artisan Mills']);
    }

    /**
     * Test admin can view product create page.
     */
    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/products/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Product');
        $response->assertSee('Basic Information');
        $response->assertSee('Pricing & Profit Margin');
        $response->assertSee('Bakery & Bread');
    }

    /**
     * Test admin can create product with valid data.
     */
    public function test_admin_can_create_product_and_initial_inventory_log(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Artisan Sourdough Loaf',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'cost_price' => '2.50',
            'selling_price' => '5.99',
            'stock_quantity' => 45,
            'min_stock_threshold' => 10,
            'unit' => 'pcs',
            'weight' => '600g',
            'short_description' => 'Crusty sourdough bread.',
            'is_active' => '1',
            'is_featured' => '1',
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('products', [
            'name' => 'Artisan Sourdough Loaf',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'selling_price' => 5.99,
            'stock_quantity' => 45,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $product = Product::where('name', 'Artisan Sourdough Loaf')->first();
        $this->assertNotNull($product->sku);
        $this->assertEquals('artisan-sourdough-loaf', $product->slug);

        // Verify initial inventory audit log
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 45,
            'previous_stock' => 0,
            'current_stock' => 45,
        ]);
    }

    /**
     * Test admin can view edit page.
     */
    public function test_admin_can_view_edit_page(): void
    {
        $product = Product::factory()->create([
            'name' => 'Whole Milk 1L',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Product: Whole Milk 1L');
        $response->assertSee('Whole Milk 1L');
    }

    /**
     * Test admin can update product and stock adjustments are audited.
     */
    public function test_admin_can_update_product_and_audit_stock_delta(): void
    {
        $product = Product::factory()->create([
            'name' => 'Organic Butter 250g',
            'stock_quantity' => 20,
            'selling_price' => 4.50,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/products/{$product->id}", [
            'name' => 'Organic Grass-Fed Butter 250g',
            'category_id' => $this->category->id,
            'cost_price' => '2.00',
            'selling_price' => '4.99',
            'stock_quantity' => 35, // +15 stock intake
            'min_stock_threshold' => 8,
            'unit' => 'pack',
            'is_active' => '1',
            'is_featured' => '0',
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Organic Grass-Fed Butter 250g',
            'selling_price' => 4.99,
            'stock_quantity' => 35,
        ]);

        // Verify stock adjustment was logged
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 15,
            'previous_stock' => 20,
            'current_stock' => 35,
        ]);
    }
}
