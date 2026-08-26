<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\User;
use App\Services\BannerEngine\Versioning\BannerVersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerVersioningAndPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_version_creation_increments_version_numbers(): void
    {
        $banner = Banner::create([
            'title' => 'Lifecycle Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $manager = new BannerVersionManager();

        $v1 = $manager->createDraft($banner, ['headline' => 'Title 1'], 'First version');
        $v2 = $manager->createDraft($banner, ['headline' => 'Title 2'], 'Second version');
        $v3 = $manager->createDraft($banner, ['headline' => 'Title 3'], 'Third version');

        $this->assertEquals(1, $v1->version_number);
        $this->assertEquals(2, $v2->version_number);
        $this->assertEquals(3, $v3->version_number);
    }

    public function test_publishing_lifecycle_promotes_draft_and_archives_previous(): void
    {
        $banner = Banner::create([
            'title' => 'Publishing Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'position' => 'hero_slider',
        ]);

        $manager = new BannerVersionManager();

        $v1 = $manager->createDraft($banner, ['headline' => 'Version 1 Copy']);
        $manager->publishVersion($v1);

        $this->assertEquals(BannerVersion::STATUS_PUBLISHED, $v1->fresh()->status);
        $this->assertNotNull($v1->fresh()->published_at);
        $this->assertEquals($v1->id, $banner->fresh()->active_version_id);

        // Create and publish v2
        $v2 = $manager->createDraft($banner, ['headline' => 'Version 2 Copy']);
        $manager->publishVersion($v2);

        $this->assertEquals(BannerVersion::STATUS_ARCHIVED, $v1->fresh()->status);
        $this->assertEquals(BannerVersion::STATUS_PUBLISHED, $v2->fresh()->status);
        $this->assertEquals($v2->id, $banner->fresh()->active_version_id);
    }

    public function test_one_click_rollback_restores_historical_content_identically(): void
    {
        $banner = Banner::create([
            'title' => 'Rollback Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $manager = new BannerVersionManager();

        $v1 = $manager->createDraft($banner, ['headline' => 'Organic Fresh Apples']);
        $manager->publishVersion($v1);

        $v2 = $manager->createDraft($banner, ['headline' => 'Summer Sale Mangoes']);
        $manager->publishVersion($v2);

        $this->assertEquals('Summer Sale Mangoes', $banner->fresh()->activeVersion->field_values['headline']);

        // Rollback to v1
        $restored = $manager->rollbackToVersion($banner, $v1);

        $this->assertEquals(3, $restored->version_number);
        $this->assertEquals(BannerVersion::STATUS_PUBLISHED, $restored->status);
        $this->assertEquals('Organic Fresh Apples', $restored->field_values['headline']);
        $this->assertEquals('Organic Fresh Apples', $banner->fresh()->activeVersion->field_values['headline']);
    }

    public function test_scheduled_publishing_window_evaluates_active_state(): void
    {
        $manager = new BannerVersionManager();

        // 1. Future scheduled banner
        $futureBanner = Banner::create([
            'title' => 'Future Banner',
            'image' => '/storage/placeholder.png',
            'is_active' => true,
            'starts_at' => now()->addDays(2),
        ]);
        $this->assertFalse($manager->isBannerActiveNow($futureBanner));

        // 2. Expired banner
        $expiredBanner = Banner::create([
            'title' => 'Expired Banner',
            'image' => '/storage/placeholder.png',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);
        $this->assertFalse($manager->isBannerActiveNow($expiredBanner));

        // 3. Currently active banner
        $activeBanner = Banner::create([
            'title' => 'Active Banner',
            'image' => '/storage/placeholder.png',
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(5),
        ]);
        $this->assertTrue($manager->isBannerActiveNow($activeBanner));
    }

    public function test_admin_version_history_route_renders_version_timeline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $banner = Banner::create([
            'title' => 'Timeline Admin Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $manager = new BannerVersionManager();
        $manager->createDraft($banner, ['headline' => 'V1 Test']);

        $response = $this->actingAs($admin)->get(route('admin.banners.versions', $banner->id));

        $response->assertStatus(200);
        $response->assertSee('Version History: Timeline Admin Banner');
        $response->assertSee('Version 1');
    }
}
