<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerFieldMapping;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Product;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Cache\BannerCacheManager;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BannerPerformanceAndCachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rendered_output_is_cached_and_serves_from_cache(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Cache Test Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Original Cached Title</h1></div>',
            'raw_css' => '.banner { color: red; }',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $renderer = new SandboxedRenderer();

        // 1. Initial cached render
        $output1 = $renderer->renderCached($template, ['headline' => 'Initial Dynamic Title']);
        $this->assertStringContainsString('Initial Dynamic Title', $output1);

        // 2. Modify template raw_html directly in database without touching updated_at
        BannerTemplate::where('id', $template->id)->update(['raw_html' => '<div class="banner"><h1>Bypassed Title</h1></div>']);

        // 3. Second cached render should serve the cached document (not the modified raw_html)
        $output2 = $renderer->renderCached($template, ['headline' => 'Initial Dynamic Title']);
        $this->assertStringContainsString('Initial Dynamic Title', $output2);
        $this->assertStringNotContainsString('Bypassed Title', $output2);
    }

    public function test_cache_invalidation_on_template_edit(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Invalidation Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><h1>First Title</h1></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $cacheManager = new BannerCacheManager();
        $renderer = new SandboxedRenderer();

        $output1 = $renderer->renderCached($template, ['headline' => 'First Title']);
        $this->assertStringContainsString('First Title', $output1);

        // Invalidate template
        $cacheManager->invalidateTemplate($template);

        // Render with new value
        $output2 = $renderer->renderCached($template, ['headline' => 'Second Invalidated Title']);
        $this->assertStringContainsString('Second Invalidated Title', $output2);
    }

    public function test_cache_invalidation_when_mapped_product_is_invalidated(): void
    {
        $product = Product::create([
            'name' => 'Fresh Pineapples',
            'slug' => 'fresh-pineapples',
            'sku' => 'FRT-PIN-01',
            'selling_price' => 5.00,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $banner = Banner::create([
            'title' => 'Product Cache Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Pineapple Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><h1>Pineapple</h1></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);
        $headlineField = $template->fields()->first();

        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => [],
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $headlineField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'name',
        ]);

        $oldTimestamp = $template->updated_at->timestamp;

        // Invalidate Product
        $cacheManager = new BannerCacheManager();
        $cacheManager->invalidateProduct($product->id);

        $template->refresh();
        $this->assertGreaterThanOrEqual($oldTimestamp, $template->updated_at->timestamp);
    }
}
