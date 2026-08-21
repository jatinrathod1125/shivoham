<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBrandTest extends TestCase
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
     * Test guest cannot access brands index.
     */
    public function test_guest_cannot_access_brands(): void
    {
        $response = $this->get('/admin/brands');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view brands index.
     */
    public function test_admin_can_view_brands_index(): void
    {
        Brand::factory()->create(['name' => 'Organic Valley']);

        $response = $this->actingAs($this->admin)->get('/admin/brands');

        $response->assertStatus(200);
        $response->assertSee('Brands');
        $response->assertSee('Organic Valley');
        $response->assertSee('Total Brands');
    }

    /**
     * Test admin can view create brand page.
     */
    public function test_admin_can_view_create_brand_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/brands/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Brand');
        $response->assertSee('Brand Information');
    }

    /**
     * Test admin can create brand with valid data.
     */
    public function test_admin_can_create_brand(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/brands', [
            'name' => 'Kerrygold Dairy',
            'slug' => 'kerrygold-dairy',
            'website' => 'https://www.kerrygold.com',
            'description' => 'Pure Irish butter and cheeses.',
            'is_active' => '1',
            'is_featured' => '1',
        ]);

        $response->assertRedirect('/admin/brands');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('brands', [
            'name' => 'Kerrygold Dairy',
            'slug' => 'kerrygold-dairy',
            'website' => 'https://www.kerrygold.com',
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    /**
     * Test admin can edit and update brand.
     */
    public function test_admin_can_edit_and_update_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Old Brand Name']);

        $response = $this->actingAs($this->admin)->get("/admin/brands/{$brand->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Old Brand Name');

        $updateResponse = $this->actingAs($this->admin)->put("/admin/brands/{$brand->id}", [
            'name' => 'Updated Brand Name',
            'slug' => 'updated-brand-name',
            'website' => 'https://www.updated.com',
            'description' => 'Updated company bio.',
            'is_active' => '1',
            'is_featured' => '0',
        ]);

        $updateResponse->assertRedirect('/admin/brands');
        $updateResponse->assertSessionHas('toast_success');

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand Name',
            'slug' => 'updated-brand-name',
            'is_featured' => false,
        ]);
    }

    /**
     * Test quick AJAX status toggle.
     */
    public function test_admin_can_toggle_brand_status_via_ajax(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/brands/{$brand->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($brand->fresh()->is_active);
    }

    /**
     * Test quick AJAX featured toggle.
     */
    public function test_admin_can_toggle_brand_featured_via_ajax(): void
    {
        $brand = Brand::factory()->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)->postJson("/admin/brands/{$brand->id}/toggle-featured");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_featured' => true]);
        $this->assertTrue($brand->fresh()->is_featured);
    }

    /**
     * Test brand with assigned products cannot be deleted.
     */
    public function test_brand_with_products_cannot_be_deleted(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->create(['brand_id' => $brand->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/admin/brands/{$brand->id}");

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    /**
     * Test empty brand can be deleted.
     */
    public function test_empty_brand_can_be_deleted(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/brands/{$brand->id}");

        $response->assertRedirect('/admin/brands');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}
