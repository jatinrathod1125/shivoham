<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Extend banners table for universal dynamic templates
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (!Schema::hasColumn('banners', 'banner_type')) {
                    $table->string('banner_type', 30)->default('standard')->after('link');
                }
                if (!Schema::hasColumn('banners', 'current_template_id')) {
                    $table->unsignedBigInteger('current_template_id')->nullable()->after('banner_type');
                }
                if (!Schema::hasColumn('banners', 'active_version_id')) {
                    $table->unsignedBigInteger('active_version_id')->nullable()->after('current_template_id');
                }
            });
        }

        // 2. banner_templates
        if (!Schema::hasTable('banner_templates')) {
            Schema::create('banner_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banner_id')->nullable()->constrained('banners')->nullOnDelete();
                $table->string('name');
                $table->string('import_source', 50)->default('zip'); // zip, html, image
                $table->string('entry_file', 255)->nullable()->default('index.html');
                $table->longText('raw_html');
                $table->longText('raw_css')->nullable();
                $table->longText('raw_js')->nullable();
                $table->json('asset_manifest')->nullable();
                $table->json('dynamic_schema')->nullable();
                $table->json('viewports')->nullable();
                $table->json('render_metadata')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['banner_id', 'is_active']);
            });
        }

        // 3. banner_versions
        if (!Schema::hasTable('banner_versions')) {
            Schema::create('banner_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
                $table->foreignId('template_id')->nullable()->constrained('banner_templates')->nullOnDelete();
                $table->integer('version_number')->default(1);
                $table->string('status', 30)->default('draft'); // draft, published, archived
                $table->json('field_values')->nullable();
                $table->json('template_snapshot')->nullable();
                $table->string('change_summary', 255)->nullable();
                $table->dateTime('published_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['banner_id', 'version_number']);
                $table->index(['banner_id', 'status']);
            });
        }

        // 4. banner_fields
        if (!Schema::hasTable('banner_fields')) {
            Schema::create('banner_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('banner_templates')->cascadeOnDelete();
                $table->string('field_key', 50); // e.g. fld_8f3a91
                $table->string('semantic_role', 50)->default('unknown');
                $table->string('label', 255);
                $table->string('field_type', 50)->default('text'); // text, image, video, price, cta, product, date, unknown
                $table->text('default_value')->nullable();
                $table->text('dom_path')->nullable();
                $table->text('selector')->nullable();
                $table->string('text_fingerprint', 255)->nullable();
                $table->text('element_fingerprint')->nullable();
                $table->decimal('confidence_score', 5, 4)->default(1.0000);
                $table->string('confidence_status', 30)->default('auto_accept'); // auto_accept, review_recommended, needs_review, unknown
                $table->text('detection_reason')->nullable();
                $table->boolean('is_editable')->default(true);
                $table->boolean('is_locked')->default(false);
                $table->json('validation_rules')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['template_id', 'field_key']);
                $table->index(['template_id', 'semantic_role', 'is_editable']);
            });
        }

        // 5. banner_field_mappings
        if (!Schema::hasTable('banner_field_mappings')) {
            Schema::create('banner_field_mappings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banner_field_id')->constrained('banner_fields')->cascadeOnDelete();
                $table->foreignId('banner_version_id')->nullable()->constrained('banner_versions')->cascadeOnDelete();
                $table->string('mapping_type', 30)->default('static'); // static, product, category, brand, offer
                $table->text('static_value')->nullable();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('product_attribute', 100)->nullable();
                $table->text('fallback_value')->nullable();
                $table->timestamps();

                $table->index(['banner_version_id', 'banner_field_id']);
            });
        }

        // 6. banner_assets
        if (!Schema::hasTable('banner_assets')) {
            Schema::create('banner_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->nullable()->constrained('banner_templates')->cascadeOnDelete();
                $table->string('original_filename', 255);
                $table->string('stored_path', 500);
                $table->string('url', 500);
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('file_size');
                $table->string('file_hash', 64)->nullable();
                $table->string('asset_type', 50)->default('image'); // image, video, font, stylesheet, script, model, other
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['template_id', 'asset_type']);
            });
        }

        // 7. banner_analyses
        if (!Schema::hasTable('banner_analyses')) {
            Schema::create('banner_analyses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('banner_templates')->cascadeOnDelete();
                $table->string('analysis_engine', 50)->default('dom_heuristic'); // dom_heuristic, multimodal_ai, ocr_vision
                $table->string('status', 30)->default('completed'); // pending, processing, completed, failed
                $table->decimal('overall_confidence', 5, 4)->default(1.0000);
                $table->integer('elements_detected_count')->default(0);
                $table->integer('editable_elements_count')->default(0);
                $table->integer('locked_elements_count')->default(0);
                $table->longText('raw_prompt')->nullable();
                $table->longText('raw_response')->nullable();
                $table->json('detected_schema')->nullable();
                $table->json('reviewer_overrides')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['template_id', 'status']);
            });
        }

        // 8. banner_publications
        if (!Schema::hasTable('banner_publications')) {
            Schema::create('banner_publications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
                $table->foreignId('version_id')->constrained('banner_versions')->cascadeOnDelete();
                $table->string('position', 50)->default('home_hero');
                $table->json('target_audience')->nullable();
                $table->longText('cached_html')->nullable();
                $table->longText('cached_css')->nullable();
                $table->boolean('is_active')->default(true);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->unsignedBigInteger('impressions_count')->default(0);
                $table->unsignedBigInteger('clicks_count')->default(0);
                $table->timestamps();

                $table->index(['position', 'is_active', 'starts_at', 'expires_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_publications');
        Schema::dropIfExists('banner_analyses');
        Schema::dropIfExists('banner_assets');
        Schema::dropIfExists('banner_field_mappings');
        Schema::dropIfExists('banner_fields');
        Schema::dropIfExists('banner_versions');
        Schema::dropIfExists('banner_templates');

        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (Schema::hasColumn('banners', 'active_version_id')) {
                    $table->dropColumn('active_version_id');
                }
                if (Schema::hasColumn('banners', 'current_template_id')) {
                    $table->dropColumn('current_template_id');
                }
                if (Schema::hasColumn('banners', 'banner_type')) {
                    $table->dropColumn('banner_type');
                }
            });
        }
    }
};
