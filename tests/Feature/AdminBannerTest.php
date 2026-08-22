<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerTest extends TestCase
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
     * Test guest cannot access banners index.
     */
    public function test_guest_cannot_access_banners(): void
    {
        $response = $this->get('/admin/banners');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view banners index with KPIs and placement position tabs.
     */
    public function test_admin_can_view_banners_index(): void
    {
        Banner::factory()->create([
            'title' => 'Mega Organic Sale Hero',
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/banners');

        $response->assertStatus(200);
        $response->assertSee('Promotional Banners');
        $response->assertSee('Mega Organic Sale Hero');
        $response->assertSee('Home Hero Slider');
        $response->assertSee('Total Banners');
        $response->assertSee('Hero Sliders');
    }

    /**
     * Test admin can create banner with uploaded image.
     */
    public function test_admin_can_create_banner(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('banner_hero.jpg', 1200, 400);

        $response = $this->actingAs($this->admin)->post('/admin/banners', [
            'title' => 'Fresh Farm Berries Season',
            'subtitle' => 'Handpicked fresh berries on 30% discount',
            'link' => '/categories/berries',
            'position' => 'home_hero',
            'sort_order' => 1,
            'image' => $file,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/banners');
        $response->assertSessionHas('toast_success');

        $banner = Banner::where('title', 'Fresh Farm Berries Season')->first();
        $this->assertNotNull($banner);
        $this->assertEquals('home_hero', $banner->position);
        $this->assertTrue($banner->is_active);
        $this->assertNotNull($banner->image);
    }

    /**
     * Test admin can update banner.
     */
    public function test_admin_can_update_banner(): void
    {
        $banner = Banner::factory()->create([
            'title' => 'Old Banner Title',
            'position' => Banner::POSITION_SIDEBAR,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/banners/{$banner->id}", [
            'title' => 'Updated Banner Title',
            'subtitle' => 'New Subtitle text',
            'link' => '/offers',
            'position' => 'promotional_bar',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/banners');
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Updated Banner Title',
            'position' => 'promotional_bar',
            'sort_order' => 5,
        ]);
    }

    /**
     * Test AJAX toggle banner status.
     */
    public function test_admin_can_toggle_banner_status(): void
    {
        $banner = Banner::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson("/admin/banners/{$banner->id}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($banner->fresh()->is_active);
    }

    /**
     * Test admin can delete banner.
     */
    public function test_admin_can_delete_banner(): void
    {
        $banner = Banner::factory()->create(['title' => 'Delete Me Banner']);

        $response = $this->actingAs($this->admin)->delete("/admin/banners/{$banner->id}");

        $response->assertRedirect('/admin/banners');
        $response->assertSessionHas('toast_success');
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }
}
