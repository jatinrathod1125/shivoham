<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Models\User;
use App\Services\BannerEngine\Import\PsdImportService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerPsdImportEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $psdFixturePath;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'email' => 'admin_psd@shivoham.store',
            'role' => 'admin',
        ]);

        $this->psdFixturePath = $this->createMockPsdFile(1200, 600, 'Organic Almonds Hero Banner');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->psdFixturePath)) {
            @unlink($this->psdFixturePath);
        }
        parent::tearDown();
    }

    /**
     * Helper to create a valid binary PSD file mock for testing.
     */
    protected function createMockPsdFile(int $width, int $height, string $title): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_psd_') . '.psd';
        $handle = fopen($tempPath, 'wb');

        // 1. Header (26 bytes)
        fwrite($handle, '8BPS'); // Signature
        fwrite($handle, pack('n', 1)); // Version 1
        fwrite($handle, str_repeat("\x00", 6)); // Reserved
        fwrite($handle, pack('n', 3)); // Channels: 3 (RGB)
        fwrite($handle, pack('N', $height)); // Height
        fwrite($handle, pack('N', $width)); // Width
        fwrite($handle, pack('n', 8)); // Depth: 8-bit
        fwrite($handle, pack('n', 3)); // ColorMode: RGB

        // 2. Color Mode Data
        fwrite($handle, pack('N', 0)); // Length 0

        // 3. Image Resources
        fwrite($handle, pack('N', 0)); // Length 0

        // 4. Layer and Mask Information Section
        // Build mock layer data
        $layerBuffer = '';
        $layerCount = 3;
        $layerBuffer .= pack('n', $layerCount); // 3 layers

        $layerRecords = [
            [
                'name' => 'Main Headline',
                'top' => 100,
                'left' => 80,
                'bottom' => 200,
                'right' => 600,
                'is_text' => true,
                'text' => '100% Pure California Almonds',
            ],
            [
                'name' => 'Product Pack Cutout',
                'top' => 80,
                'left' => 650,
                'bottom' => 520,
                'right' => 1100,
                'is_text' => false,
                'text' => null,
            ],
            [
                'name' => 'CTA Button Shop Now',
                'top' => 450,
                'left' => 80,
                'bottom' => 520,
                'right' => 320,
                'is_text' => true,
                'text' => 'Order Fresh Today',
            ],
        ];

        foreach ($layerRecords as $lr) {
            $layerBuffer .= pack('N', $lr['top']);
            $layerBuffer .= pack('N', $lr['left']);
            $layerBuffer .= pack('N', $lr['bottom']);
            $layerBuffer .= pack('N', $lr['right']);
            $layerBuffer .= pack('n', 3); // 3 channels

            // Channel info (3 channels: 0, 1, 2)
            $layerBuffer .= pack('nN', 0, 0);
            $layerBuffer .= pack('nN', 1, 0);
            $layerBuffer .= pack('nN', 2, 0);

            $layerBuffer .= '8BIM'; // Blend signature
            $layerBuffer .= 'norm'; // Normal blend
            $layerBuffer .= chr(255); // Opacity 255
            $layerBuffer .= chr(0); // Clipping
            $layerBuffer .= chr(0); // Flags (visible)
            $layerBuffer .= chr(0); // Filler

            // Extra data
            $extraContent = '';
            // Mask length 0
            $extraContent .= pack('N', 0);
            // Blending ranges length 0
            $extraContent .= pack('N', 0);

            // Layer Name (Pascal string + 4-byte padding)
            $nameStr = $lr['name'];
            $nameLen = strlen($nameStr);
            $extraContent .= chr($nameLen) . $nameStr;
            $pad = (4 - (($nameLen + 1) % 4)) % 4;
            if ($pad > 0) {
                $extraContent .= str_repeat("\x00", $pad);
            }

            // If text layer, embed TySh descriptor
            if ($lr['is_text'] && $lr['text']) {
                $textDescriptor = "8BIMTySh" . pack('N', strlen($lr['text']) + 16) . " (Txt " . $lr['text'] . " )";
                $extraContent .= $textDescriptor;
            }

            $layerBuffer .= pack('N', strlen($extraContent)) . $extraContent;
        }

        // Write Layer Info
        $layerInfoLen = strlen($layerBuffer);
        $layerSection = pack('N', $layerInfoLen) . $layerBuffer;
        fwrite($handle, pack('N', strlen($layerSection)) . $layerSection);

        // 5. Global Image Data (raw RGB blank)
        fwrite($handle, pack('n', 0)); // Uncompressed raw

        fclose($handle);
        return $tempPath;
    }

    public function test_it_correctly_parses_binary_psd_file_structure_and_dimensions()
    {
        $importer = new PsdImportService();
        $structure = $importer->parsePsdStructure($this->psdFixturePath);

        $this->assertEquals(1200, $structure['width']);
        $this->assertEquals(600, $structure['height']);
        $this->assertEquals('RGB', $structure['color_mode']);
        $this->assertGreaterThanOrEqual(3, count($structure['layers']));

        $layerNames = array_column($structure['layers'], 'name');
        $this->assertContains('Main Headline', $layerNames);
        $this->assertContains('Product Pack Cutout', $layerNames);
        $this->assertContains('CTA Button Shop Now', $layerNames);
    }

    public function test_it_imports_psd_file_and_extracts_html_css_and_dynamic_fields()
    {
        $file = new UploadedFile($this->psdFixturePath, 'california_almonds.psd', 'image/vnd.adobe.photoshop', null, true);

        $importer = new PsdImportService();
        $template = $importer->importPsd($file, [
            'name' => 'California Almonds Photoshop Hero',
        ]);

        $this->assertInstanceOf(BannerTemplate::class, $template);
        $this->assertEquals(BannerTemplate::SOURCE_PSD, $template->import_source);
        $this->assertEquals('California Almonds Photoshop Hero', $template->name);
        $this->assertNotEmpty($template->raw_html);
        $this->assertNotEmpty($template->raw_css);

        // Verify HTML elements
        $this->assertStringContainsString('psd-hero-stage', $template->raw_html);
        $this->assertStringContainsString('psd-main-headline', $template->raw_html);
        $this->assertStringContainsString('psd-cta-button', $template->raw_html);

        // Verify Assets
        $this->assertGreaterThanOrEqual(1, $template->assets()->count());

        // Verify Structural Analysis extracted editable fields
        $fields = $template->fields()->get();
        $this->assertGreaterThanOrEqual(2, $fields->count());

        $semanticRoles = $fields->pluck('semantic_role')->toArray();
        $this->assertContains('headline', $semanticRoles);
    }

    public function test_it_allows_up_to_500mb_upload_limit_configuration()
    {
        $maxLimit = Config::get('banner_engine.limits.max_psd_size_kb');
        $this->assertEquals(512000, $maxLimit); // 500MB = 512,000 KB
    }

    public function test_it_renders_psd_template_in_sandboxed_renderer_seamlessly()
    {
        $file = new UploadedFile($this->psdFixturePath, 'test_banner.psd', 'image/vnd.adobe.photoshop', null, true);

        $importer = new PsdImportService();
        $template = $importer->importPsd($file, ['name' => 'Rendered PSD Test']);

        $renderer = new SandboxedRenderer();
        $renderedHtml = $renderer->render($template);

        $this->assertStringContainsString('<!DOCTYPE html>', $renderedHtml);
        $this->assertStringContainsString('psd-hero-stage', $renderedHtml);
        $this->assertStringContainsString('Playfair Display', $renderedHtml);
        $this->assertStringContainsString('banner-resize', $renderedHtml);
    }

    public function test_it_processes_psd_import_via_admin_controller_endpoint()
    {
        $file = new UploadedFile($this->psdFixturePath, 'california_almonds_promo.psd', 'image/vnd.adobe.photoshop', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.banners.import.process'), [
            'import_type' => 'psd',
            'title' => 'California Almonds PSD Promotion',
            'position' => 'home_hero',
            'psd_file' => $file,
        ]);

        $response->assertRedirect();
        $banner = Banner::where('title', 'California Almonds PSD Promotion')->first();
        $this->assertNotNull($banner);
        $this->assertEquals(Banner::TYPE_DYNAMIC_TEMPLATE, $banner->banner_type);
        $this->assertNotNull($banner->current_template_id);
    }
}
