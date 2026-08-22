<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerBuilderTest extends TestCase
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
     * Test guest cannot access banner builder.
     */
    public function test_guest_cannot_access_banner_builder(): void
    {
        $banner = Banner::factory()->create();

        $response = $this->get("/admin/banners/{$banner->id}/builder");
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test admin can view banner builder interface.
     */
    public function test_admin_can_view_banner_builder_interface(): void
    {
        $banner = Banner::factory()->create(['title' => 'Fresh Mega Banner']);

        $response = $this->actingAs($this->admin)->get("/admin/banners/{$banner->id}/builder");

        $response->assertStatus(200);
        $response->assertSee('Visual Banner Builder');
        $response->assertSee('Fresh Mega Banner');
    }

    /**
     * Test admin can save visual design configuration via AJAX PUT.
     */
    public function test_admin_can_save_visual_design_configuration(): void
    {
        $banner = Banner::factory()->create([
            'title' => 'Initial Title',
            'image' => '/storage/banners/hero.jpg',
        ]);

        $payload = [
            'title' => 'Updated Interactive Banner Title',
            'design_config' => [
                'canvas' => [
                    'width' => 1920,
                    'height' => 700,
                    'backgroundColor' => '#0f172a',
                    'backgroundImage' => '/storage/banners/hero.jpg',
                ],
                'elements' => [
                    [
                        'id' => 'elem-h1',
                        'type' => 'text',
                        'content' => 'Farm Fresh Direct',
                        'x' => 12,
                        'y' => 25,
                        'width' => 50,
                        'height' => 15,
                        'rotation' => 0,
                        'zIndex' => 10,
                        'visible' => true,
                        'locked' => false,
                        'style' => [
                            'fontFamily' => 'Instrument Sans',
                            'fontSize' => 54,
                            'fontWeight' => 700,
                            'color' => '#ffffff',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/banners/{$banner->id}/builder", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $banner->fresh();
        $this->assertEquals('Updated Interactive Banner Title', $fresh->title);
        $this->assertEquals('#0f172a', $fresh->effective_design_config['canvas']['backgroundColor']);
        $this->assertEquals('Farm Fresh Direct', $fresh->effective_design_config['elements'][0]['content']);
    }

    /**
     * Test admin can upload auxiliary asset for visual builder.
     */
    public function test_admin_can_upload_asset_for_builder(): void
    {
        Storage::fake('public');
        $banner = Banner::factory()->create();
        $file = UploadedFile::fake()->image('sticker_leaf.png', 200, 200);

        $response = $this->actingAs($this->admin)->postJson("/admin/banners/{$banner->id}/builder/upload-asset", [
            'asset' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('url'));
    }

    /**
     * Test admin can save banner placement and scheduling window via visual builder.
     */
    public function test_admin_can_save_scheduling_and_placement_via_builder(): void
    {
        $banner = Banner::factory()->create([
            'position' => 'home_hero',
            'is_active' => true,
        ]);

        $payload = [
            'title' => 'Scheduled Supermarket Flash Sale',
            'position' => 'popup',
            'is_active' => false,
            'starts_at' => '2026-09-01 00:00:00',
            'expires_at' => '2026-09-30 23:59:59',
            'sort_order' => 5,
            'design_config' => [
                'canvas' => [
                    'width' => 1920,
                    'height' => 700,
                    'backgroundColor' => '#881337',
                ],
                'elements' => [],
            ],
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/banners/{$banner->id}/builder", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $banner->fresh();
        $this->assertEquals('popup', $fresh->position);
        $this->assertFalse($fresh->is_active);
        $this->assertEquals(5, $fresh->sort_order);
        $this->assertEquals('2026-09-01 00:00:00', $fresh->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-30 23:59:59', $fresh->expires_at->format('Y-m-d H:i:s'));
    }
}
