<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test full database seeding runs without errors and populates all core models.
     */
    public function test_database_seeder_populates_all_grocery_entities(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@grocery.local']);
        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Brand::count());
        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, Customer::count());
        $this->assertGreaterThan(0, Order::count());
        $this->assertGreaterThan(0, OrderItem::count());
        $this->assertGreaterThan(0, InventoryTransaction::count());
        $this->assertGreaterThan(0, Coupon::count());
        $this->assertGreaterThan(0, Offer::count());
        $this->assertGreaterThan(0, Banner::count());
        $this->assertGreaterThan(0, Setting::count());
    }

    /**
     * Test Category hierarchical nesting and parent-child relationship.
     */
    public function test_category_nested_relationships(): void
    {
        $parent = Category::factory()->create(['name' => 'Dairy & Eggs', 'parent_id' => null]);
        $child1 = Category::factory()->create(['name' => 'Organic Milk', 'parent_id' => $parent->id]);
        $child2 = Category::factory()->create(['name' => 'Farm Cheeses', 'parent_id' => $parent->id]);

        $this->assertCount(2, $parent->children);
        $this->assertEquals($parent->id, $child1->parent->id);
        $this->assertEquals('Dairy & Eggs', $child2->parent->name);
    }

    /**
     * Test Product pricing accessors and scopes.
     */
    public function test_product_pricing_and_stock_scopes(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 2.00,
            'selling_price' => 5.00,
            'special_price' => 3.99,
            'special_price_start' => now()->subDay(),
            'special_price_end' => now()->addDay(),
            'stock_quantity' => 4,
            'min_stock_threshold' => 10,
        ]);

        $this->assertEquals(3.99, $product->effective_price);
        $this->assertTrue($product->is_on_sale);
        $this->assertTrue($product->is_low_stock);
        $this->assertFalse($product->is_out_of_stock);
        $this->assertEquals('low_stock', $product->stock_status);
        $this->assertCount(1, Product::lowStock()->get());
    }

    /**
     * Test Order and OrderItem relationship and cascades.
     */
    public function test_order_and_items_relationship(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10.00]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => Order::STATUS_PROCESSING,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'unit_price' => 10.00,
            'quantity' => 2,
            'total' => 20.00,
        ]);

        $this->assertCount(1, $order->items);
        $this->assertEquals($customer->id, $order->customer->id);
        $this->assertEquals($product->id, $order->items->first()->product->id);
    }

    /**
     * Test Coupon discount calculations and validity.
     */
    public function test_coupon_calculations(): void
    {
        $percentCoupon = Coupon::factory()->create([
            'code' => 'TEST10',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10.00,
            'min_spend' => 20.00,
            'max_discount' => 15.00,
            'is_active' => true,
        ]);

        $this->assertTrue($percentCoupon->is_valid);
        $this->assertEquals(5.00, $percentCoupon->calculateDiscount(50.00));
        $this->assertEquals(0.00, $percentCoupon->calculateDiscount(10.00)); // Under min spend

        $fixedCoupon = Coupon::factory()->create([
            'code' => 'FIXED15',
            'type' => Coupon::TYPE_FIXED,
            'value' => 15.00,
            'min_spend' => 30.00,
            'is_active' => true,
        ]);

        $this->assertEquals(15.00, $fixedCoupon->calculateDiscount(50.00));
    }

    /**
     * Test Setting model get/set with caching.
     */
    public function test_setting_store_and_retrieve(): void
    {
        Setting::set('store_name', 'Fresh Groceries Store', 'general', 'string');
        Setting::set('enable_tax', true, 'orders', 'boolean');
        Setting::set('free_shipping_limit', 50, 'orders', 'integer');

        $this->assertEquals('Fresh Groceries Store', Setting::get('store_name'));
        $this->assertTrue(Setting::get('enable_tax'));
        $this->assertEquals(50, Setting::get('free_shipping_limit'));
        $this->assertEquals('Default', Setting::get('non_existent_key', 'Default'));
    }
}
