<?php

namespace Tests\Feature;

use App\Models\BannerAnalysis;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\CssAnalyzer;
use App\Services\BannerEngine\Analyzer\DomAnalyzer;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerDomCssAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dom_analyzer_extracts_elements_and_multi_point_fingerprints(): void
    {
        $domAnalyzer = new DomAnalyzer();

        $html = <<<HTML
<section id="hero-showcase" class="bg-gradient flex-wrap">
    <div class="header-col">
        <span class="badge uppercase">NEW ARRIVAL</span>
        <h1 class="main-heading text-4xl">Farm Fresh Avocados</h1>
        <p class="description">Handpicked organic avocados delivered daily.</p>
    </div>
    <div class="media-col">
        <img src="assets/avocado.png" alt="Ripe Avocado" class="shadow-lg" />
        <div class="price-pill"><span class="price-val">₹149</span></div>
        <button type="button" class="cta-action btn-primary">Add to Basket</button>
    </div>
</section>
HTML;

        $result = $domAnalyzer->analyzeDom($html);

        $this->assertGreaterThanOrEqual(7, $result['total_nodes']);
        $this->assertNotEmpty($result['text_nodes']);
        $this->assertNotEmpty($result['media_nodes']);
        $this->assertNotEmpty($result['interactive_nodes']);

        // Check H1 node
        $h1Nodes = array_values(array_filter($result['text_nodes'], fn($n) => $n['tag'] === 'h1'));
        $this->assertCount(1, $h1Nodes);
        $h1 = $h1Nodes[0];
        $this->assertEquals('Farm Fresh Avocados', $h1['text_content']);
        $this->assertNotEmpty($h1['dom_path']);
        $this->assertNotEmpty($h1['text_fingerprint']);
        $this->assertNotEmpty($h1['element_fingerprint']);

        // Check Button node
        $btnNodes = array_values(array_filter($result['interactive_nodes'], fn($n) => $n['tag'] === 'button'));
        $this->assertCount(1, $btnNodes);
        $btn = $btnNodes[0];
        $this->assertEquals('Add to Basket', $btn['text_content']);
    }

    public function test_css_analyzer_extracts_rules_media_queries_and_keyframes(): void
    {
        $cssAnalyzer = new CssAnalyzer();

        $css = <<<CSS
:root {
    --primary-color: #16a34a;
    --banner-radius: 16px;
}

@font-face {
    font-family: 'CustomSans';
    src: url('fonts/custom.woff2') format('woff2');
    font-weight: 700;
}

@keyframes pulseGlow {
    0% { transform: scale(1); opacity: 0.9; }
    50% { transform: scale(1.05); opacity: 1; }
    100% { transform: scale(1); opacity: 0.9; }
}

.hero-showcase {
    display: flex;
    background: var(--primary-color);
    padding: 32px;
}

.main-heading {
    font-size: 42px;
    color: #ffffff;
}

@media (max-width: 768px) {
    .hero-showcase {
        flex-direction: column;
        padding: 16px;
    }
    .main-heading {
        font-size: 28px;
    }
}
CSS;

        $result = $cssAnalyzer->analyzeCss($css);

        $this->assertGreaterThanOrEqual(2, $result['total_rules']);
        $this->assertCount(1, $result['media_queries']);
        $this->assertCount(1, $result['keyframes']);
        $this->assertCount(1, $result['font_faces']);
        $this->assertArrayHasKey('--primary-color', $result['custom_properties']);

        $mq = $result['media_queries'][0];
        $this->assertEquals('768px', $mq['breakpoint']);
        $this->assertGreaterThanOrEqual(2, $mq['rule_count']);

        $kf = $result['keyframes'][0];
        $this->assertEquals('pulseGlow', $kf['name']);

        $ff = $result['font_faces'][0];
        $this->assertEquals('CustomSans', $ff['font_family']);
    }

    public function test_structural_analysis_engine_generates_schema_and_banner_fields(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Organic Honey Promo Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => <<<HTML
<div class="promo-wrap">
    <span class="badge">FLAT 30% OFF</span>
    <h1>Pure Raw Forest Honey</h1>
    <p>Unprocessed wild honey harvested from Himalayan valleys.</p>
    <div class="price-tag"><span class="amount">$24.99</span></div>
    <img src="honey-jar.png" alt="Honey Jar" />
    <a href="/buy" class="buy-btn">Order Now</a>
</div>
HTML,
            'raw_css' => '.promo-wrap { background: #fff; }',
            'is_active' => true,
        ]);

        $engine = new StructuralAnalysisEngine();
        $analysis = $engine->analyze($template);

        $this->assertInstanceOf(BannerAnalysis::class, $analysis);
        $this->assertEquals(BannerAnalysis::STATUS_COMPLETED, $analysis->status);
        $this->assertGreaterThanOrEqual(4, $analysis->editable_elements_count);
        $this->assertGreaterThanOrEqual(0.85, $analysis->overall_confidence);

        // Verify BannerField sync in database
        $fields = $template->fields()->get();
        $this->assertGreaterThanOrEqual(4, $fields->count());

        $headline = $fields->firstWhere('semantic_role', 'headline');
        $this->assertNotNull($headline);
        $this->assertEquals('Pure Raw Forest Honey', $headline->default_value);
        $this->assertTrue($headline->is_editable);

        $price = $fields->firstWhere('semantic_role', 'price');
        $this->assertNotNull($price);
        $this->assertEquals('$24.99', $price->default_value);

        $discount = $fields->firstWhere('semantic_role', 'discount');
        $this->assertNotNull($discount);
        $this->assertEquals('FLAT 30% OFF', $discount->default_value);

        $cta = $fields->firstWhere('semantic_role', 'cta');
        $this->assertNotNull($cta);
        $this->assertEquals('Order Now', $cta->default_value);

        $image = $fields->firstWhere('semantic_role', 'product_image');
        $this->assertNotNull($image);
        $this->assertEquals('honey-jar.png', $image->default_value);
    }

    public function test_analysis_on_radically_different_html_structures(): void
    {
        $engine = new StructuralAnalysisEngine();

        // 1. Structure with zero CSS classes (Semantic only)
        $semanticOnlyTemplate = BannerTemplate::create([
            'name' => 'Zero Classes Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<header><h1>Wild Berry Smoothie</h1><p>Rich in antioxidants</p><h3>Only €4.50</h3><button>Try It</button></header>',
            'raw_css' => '',
            'is_active' => true,
        ]);

        $analysis1 = $engine->analyze($semanticOnlyTemplate);
        $this->assertGreaterThanOrEqual(3, $analysis1->editable_elements_count);
        $this->assertEquals(1, $semanticOnlyTemplate->fields()->where('semantic_role', 'headline')->count());
        $this->assertEquals(1, $semanticOnlyTemplate->fields()->where('semantic_role', 'price')->count());

        // 2. 3D Canvas / WebGL Banner Structure
        $canvasTemplate = BannerTemplate::create([
            'name' => '3D Experience Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><canvas id="three-layer"></canvas><h2>Interactive Organic Milk 3D</h2><button>Rotate Model</button></div>',
            'raw_css' => '',
            'is_active' => true,
        ]);

        $analysis2 = $engine->analyze($canvasTemplate);
        $this->assertEquals(1, $canvasTemplate->fields()->where('semantic_role', 'animation')->count());
        $this->assertEquals(0, $canvasTemplate->fields()->where('semantic_role', 'animation')->first()->is_editable);
    }
}
