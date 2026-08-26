<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerAsset;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Import\HtmlImportService;
use App\Services\BannerEngine\Import\ZipImportService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use App\Services\BannerEngine\Sanitizer\HtmlSanitizer;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BannerImportEngineTest extends TestCase
{
    use RefreshDatabase;

    protected string $testTempDir;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->testTempDir = storage_path('app/test_zip_fixtures');
        File::ensureDirectoryExists($this->testTempDir);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testTempDir)) {
            File::deleteDirectory($this->testTempDir);
        }
        parent::tearDown();
    }

    public function test_raw_html_import_with_embedded_styles_and_scripts(): void
    {
        $importer = new HtmlImportService();

        $rawMarkup = <<<HTML
<div class="custom-card-99">
    <style>
        .custom-card-99 { background: linear-gradient(135deg, #10b981, #047857); padding: 40px; color: #fff; }
        .custom-card-99 h2 { font-size: 2.5rem; }
    </style>
    <h2>Organic Farm Produce</h2>
    <p>Get 20% discount on fresh greens</p>
    <a href="/shop" class="btn-cta">Shop Now</a>
    <script>
        console.log('Banner animation initialized');
    </script>
</div>
HTML;

        $template = $importer->importRawCode($rawMarkup, null, null, ['name' => 'Organic Fresh Greens']);

        $this->assertInstanceOf(BannerTemplate::class, $template);
        $this->assertEquals(BannerTemplate::SOURCE_HTML, $template->import_source);
        $this->assertStringContainsString('Organic Farm Produce', $template->raw_html);
        $this->assertStringNotContainsString('<style>', $template->raw_html);
        $this->assertStringNotContainsString('<script>', $template->raw_html);
        $this->assertStringContainsString('.custom-card-99', $template->raw_css);
        $this->assertStringContainsString("console.log('Banner animation initialized')", $template->raw_js);
    }

    public function test_zip_import_with_full_package_and_asset_resolution(): void
    {
        $zipFile = $this->createMockBannerZip();

        $service = new ZipImportService();
        $template = $service->importZip($zipFile, ['name' => 'Supermarket Hero Banner']);

        $this->assertInstanceOf(BannerTemplate::class, $template);
        $this->assertEquals(BannerTemplate::SOURCE_ZIP, $template->import_source);
        $this->assertEquals('index.html', $template->entry_file);

        // Assets should be stored
        $this->assertGreaterThanOrEqual(3, $template->assets()->count());
        $this->assertArrayHasKey('assets/images/grocery.png', $template->asset_manifest);
        $this->assertArrayHasKey('css/theme.css', $template->asset_manifest);

        // Check asset URLs are rewritten in HTML and CSS
        $asset = $template->assets()->where('original_filename', 'assets/images/grocery.png')->first();
        $this->assertNotNull($asset);
        $this->assertStringContainsString($asset->url, $template->raw_html);
        $this->assertStringNotContainsString('src="assets/images/grocery.png"', $template->raw_html);

        $this->assertStringContainsString('.hero-banner', $template->raw_css);
        $this->assertStringContainsString('console.log("Hero loaded")', $template->raw_js);
    }

    public function test_zip_import_with_nested_folder_wrapper(): void
    {
        $zipPath = $this->testTempDir . '/nested_package.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('nested_folder/index.html', '<div><h1 class="font-bold">Nested Title</h1></div>');
        $zip->addFromString('nested_folder/style.css', '.font-bold { font-weight: bold; }');
        $zip->close();

        $service = new ZipImportService();
        $template = $service->importZip($zipPath, ['name' => 'Nested Banner Package']);

        $this->assertInstanceOf(BannerTemplate::class, $template);
        $this->assertStringContainsString('Nested Title', $template->raw_html);
        $this->assertStringContainsString('.font-bold', $template->raw_css);
    }

    public function test_zip_import_blocks_dangerous_executable_files(): void
    {
        $zipPath = $this->testTempDir . '/malicious_files.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('index.html', '<div>Safe Header</div>');
        $zip->addFromString('exploit.php', '<?php system($_GET["cmd"]); ?>');
        $zip->addFromString('sub/script.sh', '#!/bin/bash\nrm -rf /');
        $zip->addFromString('assets/image.png', 'fake png content');
        $zip->close();

        $service = new ZipImportService();
        $template = $service->importZip($zipPath);

        // Exploit.php and script.sh should be discarded and not present in assets
        $this->assertEquals(0, $template->assets()->where('original_filename', 'exploit.php')->count());
        $this->assertEquals(0, $template->assets()->where('original_filename', 'sub/script.sh')->count());
        $this->assertEquals(1, $template->assets()->where('original_filename', 'assets/image.png')->count());
    }

    public function test_sanitizer_removes_dangerous_event_handlers_and_javascript_uris(): void
    {
        $sanitizer = new HtmlSanitizer();

        $dirtyHtml = '<div class="banner"><img src="item.jpg" onerror="alert(\'xss\')" onload="steal()"><a href="javascript:alert(\'pwn\')">Click</a></div>';
        $cleanHtml = $sanitizer->sanitizeHtml($dirtyHtml);

        $this->assertStringNotContainsString('onerror', $cleanHtml);
        $this->assertStringNotContainsString('onload', $cleanHtml);
        $this->assertStringNotContainsString('javascript:alert', $cleanHtml);
        $this->assertStringContainsString('item.jpg', $cleanHtml);
        $this->assertStringContainsString('<a', $cleanHtml);
    }

    public function test_sanitizer_cleanses_svg_xxe_and_embedded_scripts(): void
    {
        $sanitizer = new HtmlSanitizer();

        $dirtySvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle cx="50" cy="50" r="40" stroke="green" fill="yellow" onload="bad()"/></svg>';
        $cleanSvg = $sanitizer->sanitizeSvg($dirtySvg);

        $this->assertStringNotContainsString('<script>', $cleanSvg);
        $this->assertStringNotContainsString('onload', $cleanSvg);
        $this->assertStringContainsString('<circle', $cleanSvg);
    }

    public function test_sandboxed_renderer_generates_isolated_html_and_iframe(): void
    {
        $template = BannerTemplate::create([
            'name' => 'Organic Fresh Showcase',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<section class="organic-box"><h1>Fresh Delivery</h1></section>',
            'raw_css' => '.organic-box { color: green; }',
            'raw_js' => 'console.log("Renderer test");',
        ]);

        $renderer = new SandboxedRenderer();

        $fullDocument = $renderer->render($template);
        $this->assertStringContainsString('<!DOCTYPE html>', $fullDocument);
        $this->assertStringContainsString('Content-Security-Policy', $fullDocument);
        $this->assertStringContainsString('.organic-box { color: green; }', $fullDocument);
        $this->assertStringContainsString('<h1>Fresh Delivery</h1>', $fullDocument);
        $this->assertStringContainsString('console.log("Renderer test");', $fullDocument);

        $iframeTag = $renderer->renderIframeTag($template, ['viewport' => 'tablet']);
        $this->assertStringContainsString('<iframe', $iframeTag);
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin"', $iframeTag);
        $this->assertStringContainsString('width: 768px', $iframeTag);
    }

    public function test_import_with_radically_different_html_structures(): void
    {
        $importer = new HtmlImportService();

        // Structure A: CSS Grid with video background
        $gridHtml = <<<HTML
<header class="promo-grid-container" data-banner-hero="true">
    <video autoplay muted loop poster="poster.jpg"><source src="promo.mp4" type="video/mp4"></video>
    <div class="grid-overlay">
        <span class="badge-pill">LIMITED EDITION</span>
        <h1 class="display-title">Exotic Dragonfruit</h1>
        <p class="tagline">Direct from tropical farms to your doorstep</p>
        <div class="pricing-wrap"><span class="currency">₹</span><span class="amount">299</span></div>
        <button type="button" class="order-trigger">Order Now</button>
    </div>
</header>
HTML;
        $templateA = $importer->importRawCode($gridHtml, '.promo-grid-container { display: grid; }', null, ['name' => 'Grid Video Banner']);
        $this->assertStringContainsString('Exotic Dragonfruit', $templateA->raw_html);
        $this->assertStringContainsString('<video', $templateA->raw_html);

        // Structure B: 3D Canvas / WebGL placeholder structure
        $webglHtml = <<<HTML
<div id="three-hero-viewport" style="position: relative; width: 100%; height: 500px;">
    <canvas id="three-canvas-layer"></canvas>
    <div class="floating-hud">
        <h3>Interactive 3D Avocado</h3>
        <span class="tag">Tap & Rotate</span>
    </div>
</div>
HTML;
        $templateB = $importer->importRawCode($webglHtml, '#three-hero-viewport { background: #111; }', 'initThreeScene();', ['name' => '3D Canvas Banner']);
        $this->assertStringContainsString('Interactive 3D Avocado', $templateB->raw_html);
        $this->assertStringContainsString('<canvas', $templateB->raw_html);
        $this->assertStringContainsString('initThreeScene();', $templateB->raw_js);
    }

    protected function createMockBannerZip(): string
    {
        $zipPath = $this->testTempDir . '/sample_banner.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/theme.css">
</head>
<body>
    <div class="hero-banner">
        <h1 class="hero-title">Fresh Grocery Deals</h1>
        <p class="hero-sub">Save up to 40% every day</p>
        <img src="assets/images/grocery.png" alt="Groceries">
        <a href="/deals" class="cta-link">Shop Deals</a>
    </div>
    <script src="js/app.js"></script>
</body>
</html>
HTML;

        $css = '.hero-banner { background: url("assets/images/bg.jpg"); display: flex; } .hero-title { font-size: 32px; }';
        $js = 'console.log("Hero loaded");';

        $zip->addFromString('index.html', $html);
        $zip->addFromString('css/theme.css', $css);
        $zip->addFromString('js/app.js', $js);
        $zip->addFromString('assets/images/grocery.png', 'fake image binary data');
        $zip->addFromString('assets/images/bg.jpg', 'fake bg binary data');
        $zip->close();

        return $zipPath;
    }
}
