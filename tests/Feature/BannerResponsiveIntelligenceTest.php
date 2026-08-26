<?php

namespace Tests\Feature;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use App\Services\BannerEngine\Responsive\ResponsiveIntelligenceEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerResponsiveIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_detects_unbroken_word_overflow_risks(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Overflow Test Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><h1>SUPEREXTRABONANZAORGANICPROMOTION2026</h1><p>Normal text description</p></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new ResponsiveIntelligenceEngine();
        $audit = $engine->auditResponsiveSafety($template);

        $this->assertFalse($audit['overflow_safe']);
        $this->assertGreaterThanOrEqual(1, $audit['total_issues_count']);

        $issue = collect($audit['issues'])->firstWhere('type', 'unbroken_word_overflow');
        $this->assertNotNull($issue);
        $this->assertEquals('warning', $issue['severity']);
    }

    public function test_audit_verifies_normal_content_as_responsive_safe(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Safe Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => <<<HTML
<div class="banner-box">
    <h1>Organic Fresh Mangoes</h1>
    <p>Cold pressed virgin coconut oil and organic honey from local farms.</p>
    <button type="button" class="order-cta">Order Now</button>
</div>
HTML,
            'raw_css' => '@media (max-width: 768px) { .banner-box { padding: 10px; } }',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $engine = new ResponsiveIntelligenceEngine();
        $audit = $engine->auditResponsiveSafety($template);

        $this->assertTrue($audit['is_responsive_safe']);
        $this->assertTrue($audit['touch_target_safe']);
        $this->assertTrue($audit['overflow_safe']);
        $this->assertTrue($audit['has_mobile_media_queries']);
    }

    public function test_responsive_safety_css_prevents_overflow_without_altering_desktop_layout(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Safety Styles Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="hero"><h1>Summer Berry Splash</h1><img src="berry.png" /></div>',
            'raw_css' => '.hero { display: flex; background: #1e293b; }',
            'is_active' => true,
        ]);

        $renderer = new SandboxedRenderer();
        $html = $renderer->render($template);

        $this->assertStringContainsString('.hero { display: flex; background: #1e293b; }', $html);
        $this->assertStringContainsString('overflow-wrap: break-word;', $html);
        $this->assertStringContainsString('max-width: 100%;', $html);
    }
}
