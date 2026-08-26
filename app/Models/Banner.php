<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Banner extends Model
{
    use HasFactory;

    public const TYPE_STANDARD = 'standard';
    public const TYPE_DYNAMIC_TEMPLATE = 'dynamic_template';

    public const POSITION_HOME_HERO = 'home_hero';
    public const POSITION_POPUP = 'popup';
    public const POSITION_SIDEBAR = 'sidebar';
    public const POSITION_CATEGORY_TOP = 'category_top';
    public const POSITION_PROMOTIONAL_BAR = 'promotional_bar';

    protected $attributes = [
        'banner_type' => self::TYPE_STANDARD,
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'banner_type',
        'current_template_id',
        'active_version_id',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'current_template_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(BannerTemplate::class, 'banner_id');
    }

    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(BannerVersion::class, 'active_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BannerVersion::class, 'banner_id')->orderBy('version_number', 'desc');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(BannerPublication::class, 'banner_id');
    }

    public function isDynamicTemplate(): bool
    {
        return $this->banner_type === self::TYPE_DYNAMIC_TEMPLATE;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id', 'desc');
    }
}
