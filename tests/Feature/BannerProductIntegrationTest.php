<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerField;
use App\Models\BannerFieldMapping;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Category;
use App\Models\Product;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Catalog\ProductBindingResolver;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerProductIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_catalog_bindings_resolve_accurately(): void
    {
        $product = Product::create([
            'name' => 'Organic Devgad Alphonso Mangoes',
            'slug' => 'organic-devgad-alphonso',
            'sku' => 'MNG-DEV-001',
            'short_description' => 'Direct from the sunny coastal groves of Devgad.',
            'cost_price' => 15.00,
            'selling_price' => 29.99,
            'special_price' => 19.99,
            'stock_quantity' => 4,
            'thumbnail' => '/storage/products/mango.png',
            'is_active' => true,
        ]);

        $template = BannerTemplate::create([
            'name' => 'Product Bound Hero',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => <<<HTML
<div class="banner">
    <h1 class="title">Farm Fresh Harvest</h1>
    <p class="desc">Naturally grown organic produce delivered fresh.</p>
    <span class="badge">20% OFF</span>
    <span class="price-val">$25.00</span>
    <img src="default.png" alt="Default Product" class="product-img" />
    <a href="/default" class="cta-btn">Buy Now</a>
</div>
HTML,
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $fields = $template->fields()->get();
        $headlineField = $fields->firstWhere('semantic_role', 'headline');
        $subtitleField = $fields->firstWhere('semantic_role', 'description');
        $priceField = $fields->firstWhere('semantic_role', 'price');
        $discountField = $fields->firstWhere('semantic_role', 'discount');
        $imgField = $fields->firstWhere('semantic_role', 'product_image');
        $ctaField = $fields->firstWhere('semantic_role', 'cta');

        $banner = Banner::create([
            'title' => 'Mango Banner',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => [],
        ]);

        // Create Mappings for each field to the product
        BannerFieldMapping::create([
            'banner_field_id' => $headlineField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'name',
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $priceField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'price',
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $discountField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'discount_percentage',
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $imgField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'primary_image',
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $ctaField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'checkout_url',
        ]);

        $resolver = new ProductBindingResolver();
        $resolved = $resolver->resolveFieldValues($template, $version);

        $this->assertEquals('Organic Devgad Alphonso Mangoes', $resolved[$headlineField->field_key]);
        $this->assertEquals('$19.99', $resolved[$priceField->field_key]);
        $this->assertEquals('33% OFF', $resolved[$discountField->field_key]);
        $this->assertEquals('/storage/products/mango.png', $resolved[$imgField->field_key]['url']);
        $this->assertStringContainsString('organic-devgad-alphonso', $resolved[$ctaField->field_key]['url']);
    }

    public function test_live_catalog_price_update_reflects_in_rendered_banner(): void
    {
        $product = Product::create([
            'name' => 'Kashmiri Organic Walnuts',
            'slug' => 'kashmiri-walnuts',
            'sku' => 'NUT-WAL-01',
            'cost_price' => 10.00,
            'selling_price' => 20.00,
            'special_price' => 15.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $template = BannerTemplate::create([
            'name' => 'Walnut Banner',
            'import_source' => BannerTemplate::SOURCE_HTML,
            'raw_html' => '<div class="banner"><h1>Walnut Title</h1><span class="price-val">$0.00</span></div>',
            'is_active' => true,
        ]);

        (new StructuralAnalysisEngine())->analyze($template);

        $priceField = $template->fields()->firstWhere('semantic_role', 'price');
        $headlineField = $template->fields()->firstWhere('semantic_role', 'headline');

        $banner = Banner::create([
            'title' => 'Walnut Campaign',
            'image' => '/storage/placeholder.png',
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
        ]);

        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => [],
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $priceField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'price',
        ]);

        BannerFieldMapping::create([
            'banner_field_id' => $headlineField->id,
            'banner_version_id' => $version->id,
            'mapping_type' => BannerFieldMapping::TYPE_PRODUCT,
            'product_id' => $product->id,
            'product_attribute' => 'name',
        ]);

        $renderer = new SandboxedRenderer();

        // 1. Initial Render
        $html1 = $renderer->render($template, $version);
        $this->assertStringContainsString('$15.00', $html1);
        $this->assertStringContainsString('Kashmiri Organic Walnuts', $html1);

        // 2. Update Product Price in Database
        $product->update(['special_price' => 11.99]);

        // 3. Re-render: Live synchronization immediately reflects new price
        $html2 = $renderer->render($template, $version);
        $this->assertStringContainsString('$11.99', $html2);
        $this->assertStringNotContainsString('$15.00', $html2);
    }
}
