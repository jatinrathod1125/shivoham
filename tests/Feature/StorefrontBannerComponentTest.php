<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StorefrontBannerComponentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test storefront banner component renders visual canvas layers properly.
     */
    public function test_storefront_banner_component_renders_visual_layers(): void
    {
        $banner = Banner::factory()->create([
            'title' => 'Organic Fresh Harvest',
            'design_config' => [
                'canvas' => [
                    'width' => 1920,
                    'height' => 700,
                    'backgroundColor' => '#064e3b',
                    'backgroundImage' => '/images/banners/hero-grocery-1.jpg',
                    'overlayColor' => '#022c22',
                    'overlayOpacity' => 35,
                ],
                'elements' => [
                    [
                        'id' => 'elem-h1',
                        'type' => 'text',
                        'content' => 'Farm Fresh Daily Harvest',
                        'x' => 8,
                        'y' => 20,
                        'width' => 50,
                        'height' => 20,
                        'rotation' => 0,
                        'zIndex' => 10,
                        'visible' => true,
                        'style' => [
                            'fontFamily' => 'Instrument Sans',
                            'fontSize' => 52,
                            'fontWeight' => 800,
                            'color' => '#ffffff',
                        ],
                    ],
                    [
                        'id' => 'elem-btn',
                        'type' => 'button',
                        'content' => 'Shop Farm Produce',
                        'url' => '/categories/fruits-vegetables',
                        'x' => 8,
                        'y' => 60,
                        'width' => 20,
                        'height' => 10,
                        'rotation' => 0,
                        'zIndex' => 11,
                        'visible' => true,
                        'style' => [
                            'backgroundColor' => '#16a34a',
                            'color' => '#ffffff',
                            'borderRadius' => 12,
                        ],
                    ],
                ],
            ],
        ]);

        $rendered = Blade::render('<x-storefront-banner :banner="$banner" />', ['banner' => $banner]);

        $this->assertStringContainsString('storefront-visual-banner', $rendered);
        $this->assertStringContainsString('Farm Fresh Daily Harvest', $rendered);
        $this->assertStringContainsString('Shop Farm Produce', $rendered);
        $this->assertStringContainsString('/categories/fruits-vegetables', $rendered);
        $this->assertStringContainsString('#064e3b', $rendered);
    }

    /**
     * Test storefront banner slider renders multiple active banners.
     */
    public function test_storefront_banner_slider_renders_active_banners(): void
    {
        Banner::factory()->create([
            'title' => 'Slider Banner 1',
            'position' => 'home_hero',
            'is_active' => true,
        ]);

        Banner::factory()->create([
            'title' => 'Slider Banner 2',
            'position' => 'home_hero',
            'is_active' => true,
        ]);

        $rendered = Blade::render('<x-banner-slider position="home_hero" />');

        $this->assertStringContainsString('storefront-banner-slider-container', $rendered);
        $this->assertStringContainsString('Slider Banner 1', $rendered);
        $this->assertStringContainsString('Slider Banner 2', $rendered);
        $this->assertStringContainsString('prevBannerSlide', $rendered);
        $this->assertStringContainsString('nextBannerSlide', $rendered);
    }
}
