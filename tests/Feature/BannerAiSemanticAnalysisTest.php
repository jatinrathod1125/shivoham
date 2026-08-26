<?php

namespace Tests\Feature;

use App\Models\BannerAnalysis;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\AiSemanticClassifier;
use App\Services\BannerEngine\Analyzer\CssAnalyzer;
use App\Services\BannerEngine\Analyzer\DomAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerAiSemanticAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_context_synthesis_builds_valid_payload(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Gourmet Organic Honey',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Wild Harvest Honey</h1><p>Pure mountain nectar</p><img src="jar.png" alt="Honey Pot"><button>Buy Now</button></div>',
            'raw_css' => '.banner { display: flex; }',
            'asset_manifest' => ['jar.png' => ['mime_type' => 'image/png']],
            'is_active' => true,
        ]);

        $classifier = new AiSemanticClassifier();
        $domData = (new DomAnalyzer())->analyzeDom($template->raw_html);
        $cssData = (new CssAnalyzer())->analyzeCss($template->raw_css);

        $context = $classifier->buildAnalysisPromptContext($template, $domData, $cssData, [], $template->asset_manifest);

        $this->assertIsArray($context);
        $this->assertArrayHasKey('task', $context);
        $this->assertArrayHasKey('taxonomy', $context);
        $this->assertArrayHasKey('candidates', $context);
        $this->assertGreaterThanOrEqual(4, count($context['candidates']));
        $this->assertContains('jar.png', $context['assets']);
    }

    public function test_ai_semantic_analysis_with_deterministic_fallback(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Fresh Kiwi Summer Deal',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => <<<HTML
<section class="kiwi-hero">
    <span class="badge">HOT DEAL</span>
    <h1 class="heading">Organic Gold Kiwis</h1>
    <p class="sub">Direct from New Zealand orchards.</p>
    <div class="price-box"><span class="price-val">$5.99 / kg</span></div>
    <img src="kiwi.png" alt="Golden Kiwi Pack" />
    <button type="button" class="order-cta">Add to Cart</button>
</section>
HTML,
            'raw_css' => '.kiwi-hero { background: #fef08a; }',
            'is_active' => true,
        ]);

        $classifier = new AiSemanticClassifier();
        $analysis = $classifier->analyze($template);

        $this->assertInstanceOf(BannerAnalysis::class, $analysis);
        $this->assertEquals(BannerAnalysis::STATUS_COMPLETED, $analysis->status);
        $this->assertGreaterThanOrEqual(5, $analysis->elements_detected_count);
        $this->assertGreaterThanOrEqual(5, $analysis->editable_elements_count);
        $this->assertGreaterThanOrEqual(0.85, $analysis->overall_confidence);

        // Check fields created in database
        $fields = $template->fields()->get();
        $this->assertGreaterThanOrEqual(5, $fields->count());

        $headline = $fields->firstWhere('semantic_role', 'headline');
        $this->assertNotNull($headline);
        $this->assertEquals('Organic Gold Kiwis', $headline->default_value);
        $this->assertTrue($headline->is_editable);
        $this->assertStringContainsString('title heading', strtolower($headline->detection_reason));

        $price = $fields->firstWhere('semantic_role', 'price');
        $this->assertNotNull($price);
        $this->assertEquals('$5.99 / kg', $price->default_value);

        $cta = $fields->firstWhere('semantic_role', 'cta');
        $this->assertNotNull($cta);
        $this->assertEquals('Add to Cart', $cta->default_value);
    }

    public function test_multimodal_analysis_preserves_locked_design_layers(): void
    {
        $template = BannerTemplate::create([
            'name' => '3D Render Hero',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><canvas id="three-scene"></canvas><h2>Interactive 3D Pineapple</h2><svg><circle r="10"/></svg><button>Explore</button></div>',
            'raw_css' => '',
            'is_active' => true,
        ]);

        $classifier = new AiSemanticClassifier();
        $analysis = $classifier->analyze($template);

        $this->assertEquals(BannerAnalysis::STATUS_COMPLETED, $analysis->status);
        $this->assertGreaterThanOrEqual(1, $analysis->locked_elements_count);

        $canvasField = $template->fields()->where('semantic_role', 'animation')->first();
        $this->assertNotNull($canvasField);
        $this->assertFalse($canvasField->is_editable);
        $this->assertTrue($canvasField->is_locked);
    }
}
