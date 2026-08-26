<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Product;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use App\Services\BannerEngine\FieldEngine\FieldExtractor;
use App\Services\BannerEngine\Preservation\DesignPreservationVerifier;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerRadicalDesignsSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected StructuralAnalysisEngine $analyzer;
    protected DynamicInjector $injector;
    protected DesignPreservationVerifier $verifier;
    protected SandboxedRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new StructuralAnalysisEngine();
        $this->injector = new DynamicInjector();
        $this->verifier = new DesignPreservationVerifier();
        $this->renderer = new SandboxedRenderer();
    }

    /**
     * Test A: Glassmorphism Hero with backdrop-filter, SVG glowing blobs and angled cards.
     */
    public function test_design_a_glassmorphism_hero(): void
    {
        $rawHtml = <<<HTML
<section class="glass-hero-section">
    <div class="blob-bg"><svg viewBox="0 0 200 200"><path fill="#F87171" d="M40,-60C50,-50,60,-40,65,-28C70,-16,70,-2,67,11C64,24,58,36,49,46C40,56,28,64,15,67C2,70,-12,68,-25,62C-38,56,-50,46,-58,34C-66,22,-70,8,-68,-6C-66,-20,-58,-34,-47,-44C-36,-54,-22,-60,-7,-59C8,-58,24,-50,40,-60Z" transform="translate(100 100)" /></svg></div>
    <div class="glass-card">
        <span class="pill-tag">Exclusive Organics</span>
        <h1 class="glass-headline">Pure Kashmiri Saffron</h1>
        <p class="glass-sub">Hand-harvested filaments directly from the high-altitude fields of Pampore.</p>
        <div class="pricing-glass">
            <span class="price-val">₹650</span>
            <span class="discount-badge">25% OFF</span>
        </div>
        <a href="/shop/saffron" class="glass-cta">Order Now</a>
    </div>
</section>
HTML;
        $rawCss = '.glass-hero-section { position: relative; } .glass-card { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.2); }';

        $template = BannerTemplate::create([
            'name' => 'Design A Glassmorphism',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => $rawCss,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $this->assertGreaterThanOrEqual(4, $template->fields()->count());

        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'Organic Himalayan Shilajit',
            'price' => '₹1,200',
            'discount' => '30% OFF',
        ]);

        $this->assertStringContainsString('Organic Himalayan Shilajit', $injected);
        $this->assertStringContainsString('₹1,200', $injected);
        $this->assertStringContainsString('30% OFF', $injected);
        $this->assertStringContainsString('class="blob-bg"', $injected);

        $report = $this->verifier->verify($template, ['headline' => 'Organic Himalayan Shilajit']);
        $this->assertTrue($report['is_preserved']);
    }

    /**
     * Test B: Neo-Brutalist Banner with high-contrast borders and thick drop-shadows.
     */
    public function test_design_b_neobrutalist_banner(): void
    {
        $rawHtml = <<<HTML
<div class="brutal-box">
    <div class="brutal-header">HOT DEAL ALERT!</div>
    <h2 class="brutal-title">RAW UNPROCESSED HONEY</h2>
    <p class="brutal-p">100% pure wild forest harvest with zero additives.</p>
    <div class="brutal-tag">SAVE 40% TODAY</div>
    <button class="brutal-btn" onclick="location.href='/shop'">CLAIM DISCOUNT</button>
</div>
HTML;
        $rawCss = '.brutal-box { border: 4px solid #000; box-shadow: 8px 8px 0px #000; background: #FEF08A; }';

        $template = BannerTemplate::create([
            'name' => 'Design B Neo Brutalist',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => $rawCss,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'RAW FOREST HONEYCOMB',
            'cta' => 'GET IT NOW',
        ]);

        $this->assertStringContainsString('RAW FOREST HONEYCOMB', $injected);
        $this->assertStringContainsString('GET IT NOW', $injected);
        $this->assertStringContainsString('class="brutal-box"', $injected);
    }

    /**
     * Test C: Organic / Botanical Hero with SVG clip-paths and floating leaves.
     */
    public function test_design_c_organic_botanical_hero(): void
    {
        $rawHtml = <<<HTML
<div class="botanical-hero">
    <div class="leaf-left"></div>
    <div class="content-cluster">
        <h1 class="botanical-h1">Nourish From Nature</h1>
        <p class="botanical-copy">Cold-pressed organic virgin coconut oil for everyday wellness.</p>
        <img src="oil-bottle.png" alt="Coconut Oil" class="product-leaf-img" />
        <a href="/oil" class="eco-cta">Explore Collection</a>
    </div>
    <div class="leaf-right"></div>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Design C Botanical',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'Pure Moringa Leaf Elixir',
        ]);

        $this->assertStringContainsString('Pure Moringa Leaf Elixir', $injected);
        $this->assertStringContainsString('class="leaf-left"', $injected);
        $this->assertStringContainsString('class="leaf-right"', $injected);
    }

    /**
     * Test D: Cyberpunk / Dark Mode Banner with neon glow and scanline overlays.
     */
    public function test_design_d_cyberpunk_neon_banner(): void
    {
        $rawHtml = <<<HTML
<div class="cyber-container">
    <div class="scanlines"></div>
    <span class="neon-badge">[ SYSTEM FLASH SALE ]</span>
    <h1 class="cyber-heading">CYBER PROTEIN 3000</h1>
    <span class="cyber-price">$49.99</span>
    <button class="cyber-cta">INITIATE PURCHASE</button>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Design D Cyberpunk',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => '.cyber-container { background: #09090b; color: #22c55e; }',
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'QUANTUM ORGANIC MATCHA',
            'price' => '$29.99',
        ]);

        $this->assertStringContainsString('QUANTUM ORGANIC MATCHA', $injected);
        $this->assertStringContainsString('$29.99', $injected);
        $this->assertStringContainsString('class="scanlines"', $injected);
    }

    /**
     * Test E: Minimalist Editorial Luxury Split Banner.
     */
    public function test_design_e_minimalist_luxury_editorial(): void
    {
        $rawHtml = <<<HTML
<section class="editorial-split">
    <div class="col-visual"><img src="luxury.jpg" alt="Luxury Aroma" /></div>
    <div class="col-text">
        <span class="edition">LIMITED HARVEST &bull; NO. 04</span>
        <h1 class="editorial-title">Artisanal Sandalwood Incense</h1>
        <p class="editorial-desc">Distilled using time-honored Vedic techniques in Mysore.</p>
        <a href="/artisanal" class="understated-link">Discover the Ritual &rarr;</a>
    </div>
</section>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Design E Luxury',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'Wild Himalayan Cedarwood Incense',
        ]);

        $this->assertStringContainsString('Wild Himalayan Cedarwood Incense', $injected);
    }

    /**
     * Test F: Festive Holiday Promotion with sparkling gradients and countdown timer mock.
     */
    public function test_design_f_festive_holiday_promotion(): void
    {
        $rawHtml = <<<HTML
<div class="diwali-celebration-hero">
    <div class="sparkles-container">✨ 🪔 ✨</div>
    <h1 class="festive-title">DIWALI FESTIVAL OF WELLNESS</h1>
    <div class="discount-callout">UP TO 50% OFF ORGANIC GIFT HAMPERS</div>
    <div class="countdown-mock">
        <span class="digit">02</span> Days <span class="digit">14</span> Hours
    </div>
    <button class="gold-cta">SEND GIFT HAMPER</button>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Design F Festive',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, [
            'headline' => 'HOLI FESTIVAL OF AYURVEDIC COLORS',
        ]);

        $this->assertStringContainsString('HOLI FESTIVAL OF AYURVEDIC COLORS', $injected);
        $this->assertStringContainsString('countdown-mock', $injected);
    }

    /**
     * Test G: 3D Three.js Interactive Canvas Showcase.
     */
    public function test_design_g_threejs_canvas_showcase(): void
    {
        $rawHtml = '<div class="stage-wrap"><canvas id="webgl-canvas"></canvas><h1 class="overlay-text">3D Bottle</h1></div>';
        $template = BannerTemplate::create([
            'name' => 'Design G 3D',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_js' => 'const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("webgl-canvas") });',
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, ['headline' => '3D Cold-Pressed Bottle']);

        $this->assertStringContainsString('<canvas id="webgl-canvas"></canvas>', $injected);
        $this->assertStringContainsString('3D Cold-Pressed Bottle', $injected);
    }

    /**
     * Test H: Background Video Loop with gradient mask.
     */
    public function test_design_h_video_loop_hero(): void
    {
        $rawHtml = <<<HTML
<div class="video-bg-section">
    <video src="farm.mp4" autoplay loop muted playsinline poster="poster.jpg"></video>
    <div class="mask-overlay">
        <h1>Farm To Table Direct</h1>
        <a href="/farm">Tour Our Farms</a>
    </div>
</div>
HTML;
        $template = BannerTemplate::create([
            'name' => 'Design H Video',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, ['headline' => 'Ethical Organic Sourcing']);

        $this->assertStringContainsString('Ethical Organic Sourcing', $injected);
        $this->assertStringContainsString('autoplay loop muted playsinline', $injected);
    }

    /**
     * Test I: Multi-Product Grid Banner with 3 cards.
     */
    public function test_design_i_multiproduct_grid_banner(): void
    {
        $rawHtml = <<<HTML
<div class="triple-grid">
    <div class="card card-1"><img src="item1.jpg" /><h3>Wild Almonds</h3><span class="p">$12</span><button>Add</button></div>
    <div class="card card-2"><img src="item2.jpg" /><h3>Walnuts</h3><span class="p">$15</span><button>Add</button></div>
    <div class="card card-3"><img src="item3.jpg" /><h3>Cashews</h3><span class="p">$18</span><button>Add</button></div>
</div>
HTML;
        $template = BannerTemplate::create([
            'name' => 'Design I Grid',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $this->assertGreaterThanOrEqual(3, $template->fields()->count());
    }

    /**
     * Test J: Flash Sale Countdown Urgent Banner.
     */
    public function test_design_j_flash_sale_countdown_banner(): void
    {
        $rawHtml = <<<HTML
<div class="flash-strip">
    <span class="urgency-badge">ENDS IN 02:45:00</span>
    <h2 class="sale-title">MIDNIGHT MADNESS FLASH SALE</h2>
    <span class="sale-discount">FLAT 50% OFF</span>
    <a href="/flash" class="flash-btn">SHOP NOW</a>
</div>
HTML;
        $template = BannerTemplate::create([
            'name' => 'Design J Flash',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $injected = $this->injector->inject($rawHtml, $template, ['headline' => 'FLASH CLEARANCE 2026']);
        $this->assertStringContainsString('FLASH CLEARANCE 2026', $injected);
    }

    /**
     * Test K: Deeply Nested Divs (12 Levels) with zero semantic tags.
     */
    public function test_design_k_deeply_nested_div_architecture(): void
    {
        $rawHtml = '<div><div><div><div><div><div><div><div><div><div><div><div><div class="target-title">Super Deep Content</div><div class="target-cta">Deep Button</div></div></div></div></div></div></div></div></div></div></div></div></div>';

        $template = BannerTemplate::create([
            'name' => 'Design K Deep Divs',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $this->assertGreaterThanOrEqual(1, $template->fields()->count());

        $firstField = $template->fields()->first();
        $injected = $this->injector->inject($rawHtml, $template, [
            $firstField->field_key => 'Deeply Injected Headline',
        ]);
        $this->assertStringContainsString('Deeply Injected Headline', $injected);
    }

    /**
     * Test L: Pure Inline CSS & Zero Class Names.
     */
    public function test_design_l_inline_css_zero_classes(): void
    {
        $rawHtml = '<div style="background:#111;padding:40px;color:#fff;"><h1 style="font-size:36px;">Inline Only Hero</h1><p style="color:#aaa;">No classes anywhere.</p><a href="/test" style="background:green;padding:10px 20px;">Click Me</a></div>';

        $template = BannerTemplate::create([
            'name' => 'Design L Inline Styles',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        $this->analyzer->analyze($template);
        $this->assertGreaterThanOrEqual(2, $template->fields()->count());

        $injected = $this->injector->inject($rawHtml, $template, ['headline' => 'Updated Inline Title']);
        $this->assertStringContainsString('Updated Inline Title', $injected);
        $this->assertStringContainsString('style="background:#111;padding:40px;color:#fff;"', $injected);
    }
}
