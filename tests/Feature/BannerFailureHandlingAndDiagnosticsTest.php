<?php

namespace Tests\Feature;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\AiSemanticClassifier;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Diagnostics\DiagnosticTelemetryService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerFailureHandlingAndDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_api_outage_falls_back_seamlessly_to_heuristic_analysis(): void
    {
        $template = BannerTemplate::create([
            'name' => 'API Fallback Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Wild Forest Honey</h1><p>100% Raw & Filtered</p><a href="/shop">Order Now</a></div>',
            'is_active' => true,
        ]);

        $classifier = new AiSemanticClassifier();
        $analysis = $classifier->analyze($template);

        $this->assertNotNull($analysis);
        $this->assertEquals('completed', $analysis->status);
        $this->assertGreaterThanOrEqual(2, $analysis->elements_detected_count);
        $this->assertNotEmpty($analysis->detected_schema);
    }

    public function test_malformed_unclosed_html_is_safely_repaired_without_crashing(): void
    {
        $brokenHtml = '<div><h1>Unclosed Headline<p>Paragraph without closing tag<img src="test.jpg" alt="Broken Tag" /> <a href="/link">Buy Now';

        $telemetry = new DiagnosticTelemetryService();
        $repaired = $telemetry->repairMalformedHtml($brokenHtml);

        $this->assertStringContainsString('Unclosed Headline</h1>', $repaired);
        $this->assertStringContainsString('Buy Now</a>', $repaired);

        $template = BannerTemplate::create([
            'name' => 'Broken HTML Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $brokenHtml,
            'is_active' => true,
        ]);

        $analyzer = new StructuralAnalysisEngine();
        $analyzer->analyze($template);

        $this->assertGreaterThanOrEqual(1, $template->fields()->count());
    }

    public function test_missing_media_generates_safe_inline_svg_placeholder(): void
    {
        $telemetry = new DiagnosticTelemetryService();
        $placeholder = $telemetry->getSafePlaceholderImage('Organic Alphonso Mangoes', 800, 500);

        $this->assertStringStartsWith('data:image/svg+xml;utf8,', $placeholder);
        $this->assertStringContainsString('Organic%20Alphonso%20Mangoes', $placeholder);
    }

    public function test_sandboxed_renderer_contains_javascript_runtime_errors(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Broken Script Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><h1>Script Error Isolation</h1></div>',
            'raw_js' => 'throw new Error("Broken animation library crashed");',
            'is_active' => true,
        ]);

        $renderer = new SandboxedRenderer();
        $rendered = $renderer->render($template);

        $this->assertStringContainsString('try {', $rendered);
        $this->assertStringContainsString('catch (err)', $rendered);
        $this->assertStringContainsString('[BannerEngine Sandbox] Script execution notice:', $rendered);
        $this->assertStringContainsString('throw new Error("Broken animation library crashed");', $rendered);
    }

    public function test_diagnostic_telemetry_records_structured_events(): void
    {
        $telemetry = new DiagnosticTelemetryService();
        $event = $telemetry->record('import', 'warning', 'Asset not found in zip archive', ['filename' => 'missing_hero.png']);

        $this->assertEquals('import', $event['subsystem']);
        $this->assertEquals('warning', $event['severity']);
        $this->assertEquals('Asset not found in zip archive', $event['message']);
        $this->assertEquals('missing_hero.png', $event['context']['filename']);
        $this->assertNotNull($event['timestamp']);
    }
}
