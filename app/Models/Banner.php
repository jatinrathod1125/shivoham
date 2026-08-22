<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public const POSITION_HOME_HERO = 'home_hero';
    public const POSITION_POPUP = 'popup';
    public const POSITION_SIDEBAR = 'sidebar';
    public const POSITION_CATEGORY_TOP = 'category_top';
    public const POSITION_PROMOTIONAL_BAR = 'promotional_bar';

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'design_config',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'design_config' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected function casts(): array
    {
        return [
            'design_config' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     * Invalidate cached banners automatically when banners are created, updated, or deleted.
     */
    protected static function booted(): void
    {
        static::saved(function (Banner $banner) {
            static::clearBannerCache($banner->position);
        });

        static::deleted(function (Banner $banner) {
            static::clearBannerCache($banner->position);
        });
    }

    /**
     * Clear cached banners across all or specific placement zones.
     */
    public static function clearBannerCache(?string $position = null): void
    {
        \Illuminate\Support\Facades\Cache::forget('storefront_banners_all_active');

        if ($position) {
            \Illuminate\Support\Facades\Cache::forget("storefront_banners_{$position}");
        } else {
            $positions = [
                self::POSITION_HOME_HERO,
                self::POSITION_POPUP,
                self::POSITION_SIDEBAR,
                self::POSITION_CATEGORY_TOP,
                self::POSITION_PROMOTIONAL_BAR,
            ];
            foreach ($positions as $pos) {
                \Illuminate\Support\Facades\Cache::forget("storefront_banners_{$pos}");
            }
        }
    }

    /**
     * Retrieve active, scheduled banners for a specific placement position using caching.
     */
    public static function getActiveByPosition(string $position, int $limit = 10): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\Cache::remember("storefront_banners_{$position}", 3600, function () use ($position, $limit) {
            return static::query()
                ->where('is_active', true)
                ->where('position', $position)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Set the design config attribute, ensuring JSON encoded storage.
     */
    public function setDesignConfigAttribute($value): void
    {
        $this->attributes['design_config'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get the design config attribute, ensuring decoded array.
     */
    public function getDesignConfigAttribute($value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Get the effective design configuration, generating a sensible default layout if none exists.
     */
    public function getEffectiveDesignConfigAttribute(): array
    {
        $config = $this->design_config;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        if (!empty($config) && is_array($config) && !empty($config['elements'])) {
            return $config;
        }

        return $this->getDefaultDesignConfig();
    }

    /**
     * Generate a default 1920x700 Canva-style visual design structure.
     */
    public function getDefaultDesignConfig(): array
    {
        return [
            'canvas' => [
                'width' => 1920,
                'height' => 700,
                'backgroundColor' => '#f8fafc',
                'backgroundImage' => $this->image ?? null,
                'overlayOpacity' => 0,
                'overlayColor' => '#000000',
            ],
            'elements' => [
                [
                    'id' => 'elem-headline-' . ($this->id ?? 'new'),
                    'type' => 'text',
                    'content' => $this->title ?: 'Fresh Organic Harvest',
                    'x' => 8,
                    'y' => 22,
                    'width' => 45,
                    'height' => 16,
                    'rotation' => 0,
                    'zIndex' => 10,
                    'visible' => true,
                    'locked' => false,
                    'style' => [
                        'fontFamily' => 'Instrument Sans',
                        'fontSize' => 52,
                        'fontWeight' => 700,
                        'lineHeight' => 1.15,
                        'letterSpacing' => -0.5,
                        'color' => '#0f172a',
                        'textAlign' => 'left',
                        'textShadow' => 'none',
                        'opacity' => 100,
                    ],
                ],
                [
                    'id' => 'elem-subtitle-' . ($this->id ?? 'new'),
                    'type' => 'text',
                    'content' => $this->subtitle ?: 'Farm-fresh groceries delivered directly to your doorstep.',
                    'x' => 8,
                    'y' => 42,
                    'width' => 42,
                    'height' => 12,
                    'rotation' => 0,
                    'zIndex' => 11,
                    'visible' => true,
                    'locked' => false,
                    'style' => [
                        'fontFamily' => 'Instrument Sans',
                        'fontSize' => 20,
                        'fontWeight' => 500,
                        'lineHeight' => 1.4,
                        'letterSpacing' => 0,
                        'color' => '#475569',
                        'textAlign' => 'left',
                        'textShadow' => 'none',
                        'opacity' => 100,
                    ],
                ],
                [
                    'id' => 'elem-cta-' . ($this->id ?? 'new'),
                    'type' => 'button',
                    'content' => 'Shop Now',
                    'url' => $this->link ?: '#',
                    'x' => 8,
                    'y' => 60,
                    'width' => 16,
                    'height' => 9,
                    'rotation' => 0,
                    'zIndex' => 12,
                    'visible' => true,
                    'locked' => false,
                    'style' => [
                        'fontFamily' => 'Instrument Sans',
                        'fontSize' => 16,
                        'fontWeight' => 600,
                        'backgroundColor' => '#16a34a',
                        'color' => '#ffffff',
                        'borderRadius' => 12,
                        'borderWidth' => 0,
                        'borderColor' => 'transparent',
                        'shadow' => 'md',
                        'paddingX' => 24,
                        'paddingY' => 12,
                    ],
                ],
            ],
        ];
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
