<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryTest extends TestCase
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
     * Test guest cannot access inventory index.
     */
    public function test_guest_cannot_access_inventory(): void
    {
        $response = $this->get('/admin/inventory');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view inventory overview with KPIs.
     */
    public function test_admin_can_view_inventory_overview(): void
    {
        $category = Category::factory()->create(['name' => 'Dairy & Milk']);
        $product = Product::factory()->create([
            'name' => 'Fresh Whole Milk 1L',
            'category_id' => $category->id,
            'stock_quantity' => 15,
            'min_stock_threshold' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/inventory');

        $response->assertStatus(200);
        $response->assertSee('Inventory Management');
        $response->assertSee('Fresh Whole Milk 1L');
        $response->assertSee('Total SKUs');
        $response->assertSee('Total Valuation');
    }

    /**
     * Test admin can record stock restock intake via adjust endpoint.
     */
    public function test_admin_can_record_stock_restock_intake(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 20,
        ]);

        $response = $this->actingAs($this->admin)->postJson('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 50,
            'reason' => 'Direct supplier batch delivery',
            'reference_id' => 'PO-98402',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'new_stock' => 70]);
        $this->assertEquals(70, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 50,
            'previous_stock' => 20,
            'current_stock' => 70,
            'reason' => 'Direct supplier batch delivery',
            'reference_id' => 'PO-98402',
        ]);
    }

    /**
     * Test admin can record stock damage deduction via adjust endpoint.
     */
    public function test_admin_can_record_stock_damage_deduction(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 30,
        ]);

        $response = $this->actingAs($this->admin)->postJson('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_DEDUCTION,
            'quantity' => 5,
            'reason' => 'Expired produce disposal',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'new_stock' => 25]);
        $this->assertEquals(25, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => InventoryTransaction::TYPE_DEDUCTION,
            'quantity' => 5,
            'previous_stock' => 30,
            'current_stock' => 25,
            'reason' => 'Expired produce disposal',
        ]);
    }

    /**
     * Test admin can view inventory audit history ledger.
     */
    public function test_admin_can_view_inventory_history_ledger(): void
    {
        $product = Product::factory()->create(['name' => 'Organic Orange Juice']);
        InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
            'type' => InventoryTransaction::TYPE_ADDITION,
            'quantity' => 20,
            'previous_stock' => 0,
            'current_stock' => 20,
            'reason' => 'Initial catalog setup',
            'reference_id' => 'INIT-001',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/inventory/history');

        $response->assertStatus(200);
        $response->assertSee('Inventory Audit History');
        $response->assertSee('Organic Orange Juice');
        $response->assertSee('Initial catalog setup');
        $response->assertSee('INIT-001');
    }
}
