<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerAnalysis;
use App\Models\BannerAsset;
use App\Models\BannerField;
use App\Models\BannerFieldMapping;
use App\Models\BannerPublication;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\BannerEngine\BannerEngineManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerEngineFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_engine_config_and_roles_are_registered(): void
    {
        $roles = BannerEngineManager::getSemanticRoles();

        $this->assertIsArray($roles);
        $this->assertArrayHasKey('headline', $roles);
        $this->assertArrayHasKey('price', $roles);
        $this->assertArrayHasKey('cta', $roles);
        $this->assertArrayHasKey('product_image', $roles);
        $this->assertArrayHasKey('background', $roles);

        $this->assertTrue($roles['headline']['editable']);
        $this->assertFalse($roles['background']['editable']);
    }

    public function test_confidence_classification_logic(): void
    {
        $this->assertEquals('auto_accept', BannerEngineManager::classifyConfidence(0.95));
        $this->assertEquals('auto_accept', BannerEngineManager::classifyConfidence(0.90));
        $this->assertEquals('review_recommended', BannerEngineManager::classifyConfidence(0.85));
        $this->assertEquals('needs_review', BannerEngineManager::classifyConfidence(0.60));
        $this->assertEquals('unknown', BannerEngineManager::classifyConfidence(0.40));
    }

    public function test_banner_foundation_model_relationships(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Create standard or dynamic banner
        $banner = Banner::create([
            'title' => 'Summer Mega Sale',
            'subtitle' => 'Up to 50% off on all organic fruits',
            'image' => 'banners/summer.jpg',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        // 2. Create template
        $template = BannerTemplate::create([
            'banner_id' => $banner->id,
            'name' => 'Glassmorphism Hero Banner',
            'import_source' => BannerTemplate::SOURCE_ZIP,
            'entry_file' => 'index.html',
            'raw_html' => '<div class="banner"><h1>Fresh Deals</h1><img src="assets/apple.png" /><button>Buy Now</button></div>',
            'raw_css' => '.banner { background: #000; color: #fff; }',
            'is_active' => true,
        ]);

        // 3. Create fields
        $fieldHeadline = BannerField::create([
            'template_id' => $template->id,
            'field_key' => 'fld_head_01',
            'semantic_role' => 'headline',
            'label' => 'Banner Headline',
            'field_type' => 'text',
            'default_value' => 'Fresh Deals',
            'confidence_score' => 0.98,
            'confidence_status' => BannerField::CONFIDENCE_AUTO_ACCEPT,
            'is_editable' => true,
        ]);

        $fieldPrice = BannerField::create([
            'template_id' => $template->id,
            'field_key' => 'fld_price_01',
            'semantic_role' => 'price',
            'label' => 'Product Price',
            'field_type' => 'price',
            'default_value' => '$9.99',
            'confidence_score' => 0.92,
            'confidence_status' => BannerField::CONFIDENCE_AUTO_ACCEPT,
            'is_editable' => true,
        ]);

        // 4. Create version
        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => [
                'fld_head_01' => 'Organic Fresh Mangoes',
                'fld_price_01' => '$4.99',
            ],
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        // 5. Connect product mapping
        $category = Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Alphonso Mango 1kg',
            'slug' => 'alphonso-mango-1kg',
            'sku' => 'FRU-MNG-001',
            'price' => 6.99,
            'sale_price' => 4.99,
            'stock_quantity' => 50,
        ]);

        $mapping = BannerFieldMapping::create([
            'banner_field_id' => $fieldHeadline->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'name',
        ]);

        // 6. Create asset & analysis
        $asset = BannerAsset::create([
            'template_id' => $template->id,
            'original_filename' => 'apple.png',
            'stored_path' => 'banner_engine/1/apple.png',
            'url' => '/storage/banner_engine/1/apple.png',
            'mime_type' => 'image/png',
            'file_size' => 10240,
            'asset_type' => BannerAsset::TYPE_IMAGE,
        ]);

        $analysis = BannerAnalysis::create([
            'template_id' => $template->id,
            'analysis_engine' => BannerAnalysis::ENGINE_DOM_HEURISTIC,
            'status' => BannerAnalysis::STATUS_COMPLETED,
            'overall_confidence' => 0.95,
            'elements_detected_count' => 3,
            'editable_elements_count' => 2,
            'locked_elements_count' => 1,
        ]);

        // 7. Publication record
        $publication = BannerPublication::create([
            'banner_id' => $banner->id,
            'version_id' => $version->id,
            'position' => Banner::POSITION_HOME_HERO,
            'is_active' => true,
        ]);

        // Assert relationships
        $this->assertCount(1, $banner->templates);
        $this->assertCount(1, $banner->versions);
        $this->assertCount(1, $banner->publications);
        $this->assertCount(2, $template->fields);
        $this->assertCount(2, $template->editableFields);
        $this->assertCount(1, $template->assets);
        $this->assertNotNull($template->latestAnalysis);
        $this->assertEquals($product->id, $mapping->product->id);
        $this->assertTrue($banner->isDynamicTemplate());
        $this->assertTrue($fieldHeadline->isHighConfidence());
    }

    public function test_legacy_banners_continue_working_without_modification(): void
    {
        $banner = Banner::create([
            'title' => 'Classic Static Banner',
            'subtitle' => 'Static subtitle',
            'image' => 'banners/classic.jpg',
            'link' => '/category/sale',
            'position' => Banner::POSITION_HOME_HERO,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertFalse($banner->isDynamicTemplate());
        $this->assertEquals('standard', $banner->banner_type);
        $this->assertEquals(1, Banner::active()->count());
    }
}
