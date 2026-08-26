<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use App\Services\BannerEngine\FieldEngine\FieldExtractor;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerDynamicFieldEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_extractor_syncs_fields_and_generates_stable_keys(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Field Extraction Test Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><h1>Fresh Watermelons</h1><p>Sweet summer treat</p><span class="price">$3.99</span><img src="wm.png"><button>Buy</button></div>',
            'is_active' => true,
        ]);

        $extractor = new FieldExtractor();
        $schema = [
            'elements' => [
                [
                    'semantic_role' => 'headline',
                    'label' => 'Main Title',
                    'dom_path' => '/div[1]/h1[1]',
                    'default_value' => 'Fresh Watermelons',
                    'confidence_score' => 0.98,
                ],
                [
                    'semantic_role' => 'price',
                    'label' => 'Item Price',
                    'dom_path' => '/div[1]/span[1]',
                    'default_value' => '$3.99',
                    'confidence_score' => 0.94,
                ],
                [
                    'semantic_role' => 'cta',
                    'label' => 'CTA Button',
                    'dom_path' => '/div[1]/button[1]',
                    'default_value' => 'Buy',
                    'confidence_score' => 0.95,
                ],
            ],
        ];

        $fields = $extractor->syncFieldsFromSchema($template, $schema);

        $this->assertCount(3, $fields);

        $headline = $fields->firstWhere('semantic_role', 'headline');
        $this->assertNotNull($headline);
        $this->assertStringStartsWith('fld_', $headline->field_key);
        $this->assertEquals(FieldExtractor::TYPE_TEXT, $headline->field_type);
        $this->assertEquals('Fresh Watermelons', $headline->default_value);

        $price = $fields->firstWhere('semantic_role', 'price');
        $this->assertEquals(FieldExtractor::TYPE_PRICE, $price->field_type);

        $cta = $fields->firstWhere('semantic_role', 'cta');
        $this->assertEquals(FieldExtractor::TYPE_CTA, $cta->field_type);
    }

    public function test_dynamic_injector_replaces_text_fields_preserving_dom_structure_and_classes(): void
    {
        $rawHtml = <<<HTML
<div id="hero-card" class="bg-emerald-600 rounded-2xl p-8 text-white shadow-xl" data-campaign="summer">
    <span class="badge uppercase font-bold text-amber-300">LIMITED DEAL</span>
    <h1 class="text-3xl font-extrabold tracking-tight">Original Farm Honey</h1>
    <p class="text-emerald-100 mt-2">100% natural unfiltered raw honey.</p>
    <div class="price-box"><span class="price-val text-2xl font-bold">$12.00</span></div>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Preservation Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $injector = new DynamicInjector();

        $injectedHtml = $injector->inject($rawHtml, $template, [
            'headline' => 'Organic Acacia Blossom Honey',
            'badge' => 'EXCLUSIVE HARVEST',
            'price' => '$15.99',
            'description' => 'Cold-extracted pure acacia honey from northern groves.',
        ]);

        // Values are replaced
        $this->assertStringContainsString('Organic Acacia Blossom Honey', $injectedHtml);
        $this->assertStringContainsString('EXCLUSIVE HARVEST', $injectedHtml);
        $this->assertStringContainsString('$15.99', $injectedHtml);
        $this->assertStringContainsString('Cold-extracted pure acacia honey', $injectedHtml);

        // Old values are removed
        $this->assertStringNotContainsString('Original Farm Honey', $injectedHtml);
        $this->assertStringNotContainsString('$12.00', $injectedHtml);

        // Surrounding CSS classes, IDs and data attributes are 100% preserved
        $this->assertStringContainsString('id="hero-card"', $injectedHtml);
        $this->assertStringContainsString('class="bg-emerald-600 rounded-2xl p-8 text-white shadow-xl"', $injectedHtml);
        $this->assertStringContainsString('data-campaign="summer"', $injectedHtml);
        $this->assertStringContainsString('class="text-3xl font-extrabold tracking-tight"', $injectedHtml);
        $this->assertStringContainsString('class="badge uppercase font-bold text-amber-300"', $injectedHtml);
    }

    public function test_dynamic_injector_replaces_images_videos_and_cta(): void
    {
        $rawHtml = <<<HTML
<section class="banner-media-box">
    <img src="old-apple.png" alt="Red Apple" class="product-hero-img" />
    <video src="old-video.mp4" poster="old-poster.jpg" autoplay loop></video>
    <a href="/old-link" class="cta-btn">Old CTA</a>
</section>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Media Replacement Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $injector = new DynamicInjector();

        $injectedHtml = $injector->inject($rawHtml, $template, [
            'product_image' => [
                'url' => 'https://cdn.example.com/fresh-mango.png',
                'alt' => 'Juicy Alphonso Mango',
            ],
            'video' => [
                'url' => 'https://cdn.example.com/mango-farm.mp4',
                'poster' => 'https://cdn.example.com/mango-poster.jpg',
            ],
            'cta' => [
                'text' => 'Order Fresh Mangoes',
                'url' => '/catalog/mangoes',
            ],
        ]);

        $this->assertStringContainsString('src="https://cdn.example.com/fresh-mango.png"', $injectedHtml);
        $this->assertStringContainsString('alt="Juicy Alphonso Mango"', $injectedHtml);
        $this->assertStringContainsString('class="product-hero-img"', $injectedHtml);

        $this->assertStringContainsString('src="https://cdn.example.com/mango-farm.mp4"', $injectedHtml);
        $this->assertStringContainsString('poster="https://cdn.example.com/mango-poster.jpg"', $injectedHtml);

        $this->assertStringContainsString('href="/catalog/mangoes"', $injectedHtml);
        $this->assertStringContainsString('Order Fresh Mangoes', $injectedHtml);
    }

    public function test_multi_reference_fallback_locators_resolve_when_dom_is_shifted(): void
    {
        $rawHtml = '<div class="wrapper"><div class="nested-box"><h1 class="main-heading">Unique Strawberry</h1></div></div>';

        $template = BannerTemplate::create([
            'name' => 'Fallback Locator Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);
        $headlineField = $template->fields()->firstWhere('semantic_role', 'headline');

        // Intentionally corrupt the primary dom_path to test selector & fingerprint fallback
        $headlineField->update([
            'dom_path' => '/div[99]/section[99]/h1[99]', // Wrong XPath
        ]);

        $injector = new DynamicInjector();
        $injectedHtml = $injector->inject($rawHtml, $template, [
            $headlineField->field_key => 'Winter Fresh Strawberries',
        ]);

        $this->assertStringContainsString('Winter Fresh Strawberries', $injectedHtml);
        $this->assertStringNotContainsString('Unique Strawberry', $injectedHtml);
    }

    public function test_sandboxed_renderer_renders_version_with_injected_values(): void
    {
        $banner = Banner::create([
            'title' => 'Version Test Banner',
            'image' => 'placeholder.jpg',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Version Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Default Title</h1><button>Default CTA</button></div>',
            'raw_css' => '.banner { color: blue; }',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $headlineField = $template->fields()->firstWhere('semantic_role', 'headline');
        $ctaField = $template->fields()->firstWhere('semantic_role', 'cta');

        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => [
                $headlineField->field_key => 'Published Mega Title',
                $ctaField->field_key => 'Shop Summer Collection',
            ],
        ]);

        $renderer = new SandboxedRenderer();
        $outputHtml = $renderer->render($template, $version);

        $this->assertStringContainsString('Published Mega Title', $outputHtml);
        $this->assertStringContainsString('Shop Summer Collection', $outputHtml);
        $this->assertStringContainsString('.banner { color: blue; }', $outputHtml);
    }
}
