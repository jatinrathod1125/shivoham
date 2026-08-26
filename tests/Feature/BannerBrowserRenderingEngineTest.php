<?php

namespace Tests\Feature;

use App\Models\BannerAsset;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Renderer\BrowserRenderingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerBrowserRenderingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_prepare_instrumented_document_contains_probe_script(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Visual Render Test Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Summer Deals</h1></div>',
            'raw_css' => '.banner { background: red; }',
            'is_active' => true,
        ]);

        $engine = new BrowserRenderingEngine();

        $docDesktop = $engine->prepareInstrumentedDocument($template, 'desktop');
        $this->assertStringContainsString('<!DOCTYPE html>', $docDesktop);
        $this->assertStringContainsString('window.__bannerEngineInspector', $docDesktop);
        $this->assertStringContainsString('collectMetrics', $docDesktop);
        $this->assertStringContainsString('<h1>Summer Deals</h1>', $docDesktop);

        $docMobile = $engine->prepareInstrumentedDocument($template, 'mobile');
        $this->assertStringContainsString('window.__bannerEngineInspector', $docMobile);
    }

    public function test_record_viewport_metrics_enriches_dynamic_schema_and_viewports(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Metrics Enrichment Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<header><h1 class="main-title">Mega Groceries</h1><button class="cta">Order</button></header>',
            'raw_css' => '.main-title { font-size: 40px; }',
            'is_active' => true,
        ]);

        // First run structural analyzer to create schema
        $analyzer = new StructuralAnalysisEngine();
        $analyzer->analyze($template);

        $engine = new BrowserRenderingEngine();

        $mockDesktopMetrics = [
            'viewport' => ['width' => 1440, 'height' => 600],
            'elements_count' => 2,
            'elements' => [
                [
                    'tag' => 'h1',
                    'dom_path' => '/header[1]/h1[1]',
                    'bounding_box' => ['x' => 40, 'y' => 60, 'width' => 600, 'height' => 80, 'top' => 60, 'bottom' => 140, 'left' => 40, 'right' => 640],
                    'computed_styles' => ['font_size' => '40px', 'font_weight' => '700', 'color' => 'rgb(0, 0, 0)', 'z_index' => 1],
                    'is_visible' => true,
                    'is_overflowing_x' => false,
                    'is_overflowing_y' => false,
                    'prominence_score' => 120000,
                ],
                [
                    'tag' => 'button',
                    'dom_path' => '/header[1]/button[1]',
                    'bounding_box' => ['x' => 40, 'y' => 160, 'width' => 180, 'height' => 50, 'top' => 160, 'bottom' => 210, 'left' => 40, 'right' => 220],
                    'computed_styles' => ['font_size' => '16px', 'font_weight' => '600', 'color' => 'rgb(255, 255, 255)', 'z_index' => 2],
                    'is_visible' => true,
                    'is_overflowing_x' => false,
                    'is_overflowing_y' => false,
                    'prominence_score' => 9000,
                ],
            ],
        ];

        $updatedTemplate = $engine->recordViewportMetrics($template, 'desktop', $mockDesktopMetrics);

        $this->assertArrayHasKey('desktop', $updatedTemplate->viewports);
        $this->assertEquals(2, $updatedTemplate->viewports['desktop']['elements_count']);

        $schemaElements = $updatedTemplate->dynamic_schema['elements'];
        $h1Element = null;
        foreach ($schemaElements as $el) {
            if ($el['dom_path'] === '/header[1]/h1[1]') {
                $h1Element = $el;
                break;
            }
        }

        $this->assertNotNull($h1Element);
        $this->assertArrayHasKey('desktop', $h1Element['viewports']);
        $this->assertEquals(600, $h1Element['viewports']['desktop']['bounding_box']['width']);
        $this->assertEquals('40px', $h1Element['viewports']['desktop']['computed_styles']['font_size']);
        $this->assertTrue($h1Element['viewports']['desktop']['is_visible']);
    }

    public function test_store_screenshot_persists_assets_and_updates_render_metadata(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Screenshot Capture Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div>Capture View</div>',
            'is_active' => true,
        ]);

        $engine = new BrowserRenderingEngine();

        // 1x1 transparent PNG mock binary
        $mockPngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        $asset = $engine->storeScreenshot($template, 'desktop', $mockPngData);

        $this->assertInstanceOf(BannerAsset::class, $asset);
        $this->assertEquals(BannerAsset::TYPE_IMAGE, $asset->asset_type);
        $this->assertTrue($asset->metadata['is_screenshot']);
        $this->assertEquals('desktop', $asset->metadata['viewport']);

        // Check template render_metadata
        $template->refresh();
        $this->assertArrayHasKey('screenshots', $template->render_metadata);
        $this->assertArrayHasKey('desktop', $template->render_metadata['screenshots']);
        $this->assertEquals($asset->url, $template->render_metadata['screenshots']['desktop']['url']);
    }
}
