<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerAnalysis;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Models\User;
use App\Services\BannerEngine\ImageMode\ImageToDesignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerImageToDesignTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'email' => 'admin@shivoham.com',
            'role' => 'admin',
        ]);
    }

    public function test_image_to_design_service_processes_png_image_and_reconstructs_template(): void
    {
        $file = UploadedFile::fake()->image('summer-deal.png', 1200, 500);

        $service = new ImageToDesignService();
        $template = $service->processImage($file, [
            'name' => 'Summer Deal Graphic Banner',
        ]);

        $this->assertInstanceOf(BannerTemplate::class, $template);
        $this->assertEquals(BannerTemplate::SOURCE_IMAGE, $template->import_source);
        $this->assertStringContainsString('img-banner-container', $template->raw_html);
        $this->assertStringContainsString('img-banner-title', $template->raw_html);
        $this->assertStringContainsString('.img-banner-container', $template->raw_css);

        // Verify Analysis
        $analysis = $template->latestAnalysis;
        $this->assertNotNull($analysis);
        $this->assertEquals(BannerAnalysis::STATUS_COMPLETED, $analysis->status);
        $this->assertEquals(BannerField::CONFIDENCE_REVIEW_RECOMMENDED, $analysis->confidence_tier);
        $this->assertLessThanOrEqual(0.89, $analysis->overall_confidence);

        // Verify Fields Created
        $fields = $template->fields()->get();
        $this->assertCount(5, $fields);

        $headline = $fields->firstWhere('semantic_role', 'headline');
        $this->assertNotNull($headline);
        $this->assertEquals(BannerField::CONFIDENCE_REVIEW_RECOMMENDED, $headline->confidence_status);
        $this->assertTrue($headline->is_editable);

        $discount = $fields->firstWhere('semantic_role', 'discount');
        $this->assertNotNull($discount);

        $cta = $fields->firstWhere('semantic_role', 'cta');
        $this->assertNotNull($cta);
    }

    public function test_image_to_design_service_rejects_invalid_file_formats(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $service = new ImageToDesignService();
        $service->processImage($invalidFile);
    }

    public function test_admin_can_import_flattened_image_via_controller_route(): void
    {
        $imageFile = UploadedFile::fake()->image('kiwi_promo.webp', 1200, 500);

        $response = $this->actingAs($this->admin)->post(route('admin.banners.import.process'), [
            'import_type' => 'image',
            'title' => 'Organic Gold Kiwi Promo',
            'position' => 'home_hero',
            'banner_image' => $imageFile,
        ]);

        $banner = Banner::where('title', 'Organic Gold Kiwi Promo')->first();
        $this->assertNotNull($banner);
        $this->assertEquals(Banner::TYPE_DYNAMIC_TEMPLATE, $banner->banner_type);

        $template = $banner->template;
        $this->assertNotNull($template);
        $this->assertEquals(BannerTemplate::SOURCE_IMAGE, $template->import_source);

        $response->assertRedirect(route('admin.banners.editor', $banner->id));
        $response->assertSessionHas('toast_success');
    }
}
