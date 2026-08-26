<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\User;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerAdminExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@shivoham.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_banner_import_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.banners.import'));

        $response->assertStatus(200);
        $response->assertSee('Universal AI Banner Import');
        $response->assertSee('Upload Banner ZIP Package');
        $response->assertSee('Paste HTML/CSS');
    }

    public function test_admin_can_import_raw_code_snippet_and_auto_generate_dynamic_banner(): void
    {
        $rawHtml = <<<HTML
<div class="promo-box">
    <h1>Organic Alphonso Mangoes</h1>
    <p>Hand-picked from Devgad orchards.</p>
    <div class="price-tag">$24.99 / dozen</div>
    <a href="/catalog/mangoes" class="btn-buy">Buy Mangoes</a>
</div>
HTML;

        $response = $this->actingAs($this->admin)->post(route('admin.banners.import.process'), [
            'import_type' => 'raw_code',
            'title' => 'Devgad Mango Season',
            'position' => 'home_hero',
            'html_code' => $rawHtml,
            'css_code' => '.promo-box { background: #fef08a; }',
        ]);

        $banner = Banner::where('title', 'Devgad Mango Season')->first();
        $this->assertNotNull($banner);
        $this->assertEquals(Banner::TYPE_DYNAMIC_TEMPLATE, $banner->banner_type);
        $this->assertNotNull($banner->current_template_id);
        $this->assertNotNull($banner->active_version_id);

        $template = $banner->template;
        $this->assertNotNull($template);
        $this->assertGreaterThanOrEqual(3, $template->fields()->count());

        $response->assertRedirect(route('admin.banners.editor', $banner->id));
        $response->assertSessionHas('toast_success');
    }

    public function test_admin_can_view_dynamic_content_editor(): void
    {
        $banner = Banner::create([
            'title' => 'Dynamic Summer Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Summer Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Wild Forest Honey</h1><button>Order Now</button></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $banner->update(['current_template_id' => $template->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.banners.editor', $banner->id));

        $response->assertStatus(200);
        $response->assertSee('Dynamic Banner: Dynamic Summer Banner');
        $response->assertSee('Live Sandboxed Preview');
        $response->assertSee('Desktop');
        $response->assertSee('Tablet');
        $response->assertSee('Mobile');
        $response->assertSee('Wild Forest Honey');
    }

    public function test_admin_can_update_dynamic_field_content_values(): void
    {
        $banner = Banner::create([
            'title' => 'Editable Honey Campaign',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Honey Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="box"><h1 class="title">Raw Pine Honey</h1><p class="desc">Pure mountain harvest</p></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);
        $banner->update(['current_template_id' => $template->id]);

        $headlineField = $template->fields()->firstWhere('semantic_role', 'headline');

        $response = $this->actingAs($this->admin)->post(route('admin.banners.update-fields', $banner->id), [
            'title' => 'Winter Acacia Honey Festival',
            'position' => 'home_hero',
            'is_active' => '1',
            'fields' => [
                $headlineField->field_key => 'Organic Acacia Gold Honey',
            ],
        ]);

        $response->assertRedirect(route('admin.banners.editor', $banner->id));
        $response->assertSessionHas('toast_success');

        $banner->refresh();
        $this->assertEquals('Winter Acacia Honey Festival', $banner->title);

        $activeVersion = $banner->activeVersion;
        $this->assertNotNull($activeVersion);
        $this->assertEquals('Organic Acacia Gold Honey', $activeVersion->field_values[$headlineField->field_key]);
    }

    public function test_admin_can_update_semantic_role_via_ajax(): void
    {
        $banner = Banner::create([
            'title' => 'Role Correction Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Role Template',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div><span>Special Harvest</span></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);
        $field = $template->fields()->first();

        $response = $this->actingAs($this->admin)->postJson(
            route('admin.banners.fields.role', ['banner' => $banner->id, 'field' => $field->id]),
            [
                'semantic_role' => 'badge',
                'is_editable' => true,
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $field->refresh();
        $this->assertEquals('badge', $field->semantic_role);
        $this->assertEquals(1.0, $field->confidence_score);
    }
}
