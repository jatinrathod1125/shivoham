<?php

namespace Tests\Feature;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Animation\MediaAndAnimationEngine;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerAnimationVideo3dTest extends TestCase
{
    use RefreshDatabase;

    public function test_css_keyframes_and_gsap_timeline_preservation(): void
    {
        $rawHtml = <<<HTML
<div class="banner-animated">
    <div class="pulse-badge">MEGA DEAL</div>
    <h1 class="slide-title">Organic Farm Fresh Berries</h1>
    <button class="cta-pulse">Grab Deal</button>
</div>
HTML;

        $rawCss = <<<CSS
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}
@keyframes slideTitle {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
.pulse-badge { animation: pulseBadge 2s infinite ease-in-out; }
.slide-title { animation: slideTitle 0.8s ease-out; }
CSS;

        $rawJs = 'gsap.timeline().from(".slide-title", { duration: 0.8, y: 30, opacity: 0 });';

        $template = BannerTemplate::create([
            'name' => 'Animated GSAP Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => $rawCss,
            'raw_js' => $rawJs,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new MediaAndAnimationEngine();
        $inspection = $engine->inspectRichMedia($template);

        $this->assertTrue($inspection['has_animations']);
        $this->assertEquals(2, $inspection['keyframes_count']);
        $this->assertContains('pulseBadge', $inspection['keyframes']);
        $this->assertContains('slideTitle', $inspection['keyframes']);
        $this->assertContains('GSAP (GreenSock Animation Platform)', $inspection['animation_frameworks']);

        // Inject dynamic values
        $injector = new DynamicInjector();
        $injected = $injector->inject($rawHtml, $template, [
            'headline' => 'Organic Royal Blueberries',
            'cta' => 'Shop Blueberries Now',
        ]);

        $this->assertStringContainsString('Organic Royal Blueberries', $injected);
        $this->assertStringContainsString('Shop Blueberries Now', $injected);
        $this->assertStringContainsString('class="pulse-badge"', $injected);
        $this->assertStringContainsString('class="slide-title"', $injected);
    }

    public function test_lottie_animation_player_discovery_and_path_update(): void
    {
        $rawHtml = <<<HTML
<div class="lottie-banner">
    <lottie-player src="/assets/animations/confetti.json" background="transparent" speed="1" loop autoplay></lottie-player>
    <h2>Celebration Harvest Sale</h2>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Lottie Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => '',
            'raw_js' => 'import "@lottiefiles/lottie-player";',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new MediaAndAnimationEngine();
        $inspection = $engine->inspectRichMedia($template);

        $this->assertEquals(1, $inspection['lottie_count']);
        $this->assertContains('Lottie (Bodymovin)', $inspection['animation_frameworks']);
    }

    public function test_video_layer_replacement_preserves_playback_attributes(): void
    {
        $rawHtml = <<<HTML
<div class="video-hero-wrap">
    <video src="old_harvest.mp4" poster="old_poster.jpg" autoplay loop muted playsinline class="hero-bg-video"></video>
    <div class="video-overlay">
        <h1>Farm Harvest Stream</h1>
        <button>Watch Live</button>
    </div>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'Video Hero Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => '.video-hero-wrap { position: relative; width: 100%; height: 500px; }',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new MediaAndAnimationEngine();
        $inspection = $engine->inspectRichMedia($template);

        $this->assertEquals(1, $inspection['videos_count']);
        $this->assertTrue($inspection['videos'][0]['autoplay']);
        $this->assertTrue($inspection['videos'][0]['loop']);
        $this->assertTrue($inspection['videos'][0]['muted']);

        // Inject new video stream URL
        $injector = new DynamicInjector();
        $injected = $injector->inject($rawHtml, $template, [
            'video' => [
                'url' => 'https://cdn.example.com/fresh-honeycomb-harvest.mp4',
                'poster' => 'https://cdn.example.com/honeycomb-poster.jpg',
            ],
            'headline' => 'Live Honeycomb Extraction 2026',
        ]);

        $this->assertStringContainsString('src="https://cdn.example.com/fresh-honeycomb-harvest.mp4"', $injected);
        $this->assertStringContainsString('poster="https://cdn.example.com/honeycomb-poster.jpg"', $injected);
        $this->assertStringContainsString('autoplay', $injected);
        $this->assertStringContainsString('loop', $injected);
        $this->assertStringContainsString('muted', $injected);
        $this->assertStringContainsString('class="hero-bg-video"', $injected);
        $this->assertStringContainsString('Live Honeycomb Extraction 2026', $injected);
    }

    public function test_threejs_webgl_canvas_scene_preservation_with_dynamic_hud_overlay(): void
    {
        $rawHtml = <<<HTML
<div id="three-stage-box">
    <canvas id="three-scene" width="1200" height="500"></canvas>
    <div class="hud-layer">
        <h1 class="hud-heading">Interactive 3D Pineapple</h1>
        <button class="hud-cta">Rotate 360</button>
    </div>
</div>
HTML;

        $rawJs = 'const scene = new THREE.Scene(); const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("three-scene") });';

        $template = BannerTemplate::create([
            'name' => '3D Three.js Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'raw_css' => '#three-stage-box { position: relative; }',
            'raw_js' => $rawJs,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new MediaAndAnimationEngine();
        $inspection = $engine->inspectRichMedia($template);

        $this->assertEquals(1, $inspection['canvas_scenes_count']);
        $this->assertTrue($inspection['canvas_scenes'][0]['is_webgl']);
        $this->assertContains('Three.js / WebGL 3D Canvas', $inspection['animation_frameworks']);

        // Injected HUD modification
        $injector = new DynamicInjector();
        $injected = $injector->inject($rawHtml, $template, [
            'headline' => 'Golden Sweet Mauricio Pineapple 3D',
        ]);

        $this->assertStringContainsString('<canvas id="three-scene" width="1200" height="500"></canvas>', $injected);
        $this->assertStringContainsString('Golden Sweet Mauricio Pineapple 3D', $injected);
    }
}
