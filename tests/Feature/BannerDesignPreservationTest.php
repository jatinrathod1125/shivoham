<?php

namespace Tests\Feature;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Preservation\DesignPreservationVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerDesignPreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complex_glassmorphism_gradient_hero_preservation(): void
    {
        $rawHtml = <<<HTML
<div id="hero-glass" class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 p-10 shadow-2xl backdrop-blur-xl">
    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">SUMMER EXCLUSIVE</span>
            <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight">Fresh Farm Oranges</h1>
            <p class="text-emerald-100 text-sm md:text-base">Rich in Vitamin C, picked directly from sunshine groves.</p>
            <div class="flex items-center gap-3">
                <span class="text-3xl font-black text-white">$4.99</span>
                <span class="text-lg line-through text-emerald-200/60">$7.99</span>
            </div>
            <a href="/shop/oranges" class="inline-block bg-white text-emerald-800 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-emerald-50 transition-transform hover:scale-105">Shop Oranges</a>
        </div>
        <div class="relative flex justify-center">
            <img src="oranges.png" alt="Oranges Basket" class="w-full max-w-sm drop-shadow-2xl transition-all duration-300" />
        </div>
    </div>
</div>
HTML;

        $rawCss = <<<CSS
#hero-glass {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}
@media (max-width: 768px) {
    #hero-glass { padding: 20px; }
}
CSS;

        $template = BannerTemplate::create([
            'name' => 'Glassmorphism Gradient Hero',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => $rawCss,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $verifier = new DesignPreservationVerifier();
        $report = $verifier->verify($template, [
            'headline' => 'Organic Nagpur Blood Oranges',
            'description' => 'Sweet ruby-red oranges fresh from organic farms.',
            'price' => '$6.49',
            'cta' => [
                'text' => 'Order Blood Oranges',
                'url' => '/catalog/nagpur-oranges',
            ],
            'product_image' => [
                'url' => 'https://cdn.example.com/blood-oranges.png',
                'alt' => 'Blood Oranges Basket',
            ],
        ]);

        $this->assertTrue($report['is_preserved']);
        $this->assertEquals(1.0, $report['structural_integrity_score']);
        $this->assertEquals(0, $report['violations_count']);
        $this->assertTrue($report['css_unaltered']);
        $this->assertTrue($report['animations_unaltered']);
        $this->assertGreaterThanOrEqual(1, $report['media_queries_preserved_count']);
        $this->assertGreaterThanOrEqual(4, $report['modified_nodes_count']);
    }

    public function test_animated_gsap_keyframes_banner_preservation(): void
    {
        $rawHtml = <<<HTML
<div class="animated-banner-wrap">
    <div class="floating-badge pulse-anim">FLASH SALE</div>
    <h2 class="title-slide">Himalayan Pink Salt</h2>
    <p class="desc-fade">Unrefined mineral-rich gourmet salt crystals.</p>
    <button type="button" class="btn-glow">Claim Deal</button>
</div>
HTML;

        $rawCss = <<<CSS
@keyframes floatBadge {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
@keyframes pulseAnim {
    0% { opacity: 0.8; }
    50% { opacity: 1; }
    100% { opacity: 0.8; }
}
.animated-banner-wrap {
    animation: floatBadge 3s ease-in-out infinite;
}
CSS;

        $template = BannerTemplate::create([
            'name' => 'Animated GSAP Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => $rawCss,
            'raw_js' => 'gsap.from(".title-slide", { duration: 1, y: 30, opacity: 0 });',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $verifier = new DesignPreservationVerifier();
        $report = $verifier->verify($template, [
            'headline' => 'Organic Himalayan Rock Salt Fine',
            'cta' => 'Buy Salt Now',
        ]);

        $this->assertTrue($report['is_preserved']);
        $this->assertEquals(2, $report['keyframes_preserved_count']);
        $this->assertEquals(0, $report['violations_count']);
    }

    public function test_3d_canvas_webgl_banner_preservation(): void
    {
        $rawHtml = <<<HTML
<div id="three-container" class="canvas-3d-wrapper">
    <canvas id="three-stage" width="1200" height="500"></canvas>
    <div class="hud-overlay">
        <h1 class="hud-title">3D Organic Avocado Experience</h1>
        <span class="hud-sub">Interactive 360 Render</span>
        <button class="hud-btn">Interact</button>
    </div>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => '3D Canvas Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => '#three-container { position: relative; width: 100%; height: 500px; }',
            'raw_js' => 'const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("three-stage") });',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $verifier = new DesignPreservationVerifier();
        $report = $verifier->verify($template, [
            'headline' => '3D Organic Hass Avocado 360 View',
            'cta' => 'Inspect 3D Model',
        ]);

        $this->assertTrue($report['is_preserved']);
        $this->assertEquals(0, $report['violations_count']);
        $this->assertEquals(1.0, $report['structural_integrity_score']);
    }
}
