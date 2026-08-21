<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
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
     * Test guest cannot access categories index.
     */
    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get('/admin/categories');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view categories index.
     */
    public function test_admin_can_view_categories_index(): void
    {
        Category::factory()->create(['name' => 'Fresh Produce']);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Categories');
        $response->assertSee('Fresh Produce');
        $response->assertSee('Total Categories');
    }

    /**
     * Test admin can view create category page.
     */
    public function test_admin_can_view_create_category_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Category');
        $response->assertSee('General Information');
    }

    /**
     * Test admin can create category with valid data.
     */
    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Organic Dairy',
            'slug' => 'organic-dairy',
            'description' => 'Pure organic dairy products.',
            'icon' => 'milk',
            'sort_order' => 1,
            'is_active' => '1',
            'is_featured' => '1',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Organic Dairy',
            'slug' => 'organic-dairy',
            'icon' => 'milk',
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    /**
     * Test admin can edit existing category.
     */
    public function test_admin_can_edit_and_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Category Name']);

        $response = $this->actingAs($this->admin)->get("/admin/categories/{$category->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Old Category Name');

        $updateResponse = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'New Category Name',
            'slug' => 'new-category-name',
            'description' => 'Updated description text.',
            'icon' => 'apple',
            'sort_order' => 5,
            'is_active' => '1',
            'is_featured' => '0',
        ]);

        $updateResponse->assertRedirect('/admin/categories');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Category Name',
            'slug' => 'new-category-name',
            'icon' => 'apple',
            'is_featured' => false,
        ]);
    }

    /**
     * Test quick AJAX status toggle.
     */
    public function test_admin_can_toggle_category_status_via_ajax(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/categories/{$category->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($category->fresh()->is_active);
    }

    /**
     * Test quick AJAX featured toggle.
     */
    public function test_admin_can_toggle_category_featured_via_ajax(): void
    {
        $category = Category::factory()->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)->postJson("/admin/categories/{$category->id}/toggle-featured");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_featured' => true]);
        $this->assertTrue($category->fresh()->is_featured);
    }

    /**
     * Test category with assigned products cannot be deleted.
     */
    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/admin/categories/{$category->id}");

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Test empty category can be deleted.
     */
    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
