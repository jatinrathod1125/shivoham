<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BannerTemplate extends Model
{
    use HasFactory;

    public const SOURCE_ZIP = 'zip';
    public const SOURCE_HTML = 'html';
    public const SOURCE_IMAGE = 'image';
    public const SOURCE_PSD = 'psd';

    protected $fillable = [
        'banner_id',
        'name',
        'import_source',
        'entry_file',
        'raw_html',
        'raw_css',
        'raw_js',
        'asset_manifest',
        'dynamic_schema',
        'viewports',
        'render_metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'asset_manifest' => 'array',
            'dynamic_schema' => 'array',
            'viewports' => 'array',
            'render_metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(BannerField::class, 'template_id')->orderBy('sort_order');
    }

    public function editableFields(): HasMany
    {
        return $this->hasMany(BannerField::class, 'template_id')
            ->where('is_editable', true)
            ->orderBy('sort_order');
    }

    public function lockedFields(): HasMany
    {
        return $this->hasMany(BannerField::class, 'template_id')
            ->where('is_editable', false)
            ->orderBy('sort_order');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BannerAsset::class, 'template_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BannerVersion::class, 'template_id');
    }

    public function latestAnalysis(): HasOne
    {
        return $this->hasOne(BannerAnalysis::class, 'template_id')->latestOfMany();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(BannerAnalysis::class, 'template_id');
    }
}
