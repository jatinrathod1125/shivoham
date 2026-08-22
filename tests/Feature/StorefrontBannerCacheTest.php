<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontBannerCacheTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getActiveByPosition caches banner results and cache is invalidated on banner save.
     */
    public function test_active_banners_are_cached_and_invalidated_on_save(): void
    {
        Cache::flush();

        $banner = Banner::factory()->create([
            'title' => 'Organic Fresh Supermarket',
            'position' => 'home_hero',
            'is_active' => true,
        ]);

        // First call populates cache
        $this->assertFalse(Cache::has('storefront_banners_home_hero'));
        $results = Banner::getActiveByPosition('home_hero');
        $this->assertTrue(Cache::has('storefront_banners_home_hero'));
        $this->assertCount(1, $results);
        $this->assertEquals('Organic Fresh Supermarket', $results->first()->title);

        // Updating the banner should clear cache automatically via booted model event
        $banner->title = 'Updated Organic Deals';
        $banner->save();

        $this->assertFalse(Cache::has('storefront_banners_home_hero'));

        // Next call repopulates with fresh data
        $freshResults = Banner::getActiveByPosition('home_hero');
        $this->assertTrue(Cache::has('storefront_banners_home_hero'));
        $this->assertEquals('Updated Organic Deals', $freshResults->first()->title);
    }

    /**
     * Test deleting banner invalidates cached placement.
     */
    public function test_deleting_banner_invalidates_cache(): void
    {
        Cache::flush();

        $banner = Banner::factory()->create([
            'title' => 'Popup Promo',
            'position' => 'popup',
            'is_active' => true,
        ]);

        Banner::getActiveByPosition('popup');
        $this->assertTrue(Cache::has('storefront_banners_popup'));

        $banner->delete();
        $this->assertFalse(Cache::has('storefront_banners_popup'));
    }
}
