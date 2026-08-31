<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to remove legacy banner database structures.
     */
    public function up(): void
    {
        // Drop child tables first to respect foreign key constraints
        Schema::dropIfExists('banner_publications');
        Schema::dropIfExists('banner_analyses');
        Schema::dropIfExists('banner_assets');
        Schema::dropIfExists('banner_field_mappings');
        Schema::dropIfExists('banner_fields');
        Schema::dropIfExists('banner_versions');
        Schema::dropIfExists('banner_templates');
        Schema::dropIfExists('banners');
    }

    /**
     * Reverse the migrations (recreate basic banners table if rolled back).
     */
    public function down(): void
    {
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image')->nullable();
                $table->string('link')->nullable();
                $table->string('position', 50)->default('home_hero');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }
};
