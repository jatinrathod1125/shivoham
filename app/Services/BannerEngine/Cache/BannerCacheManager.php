<?php

namespace App\Services\BannerEngine\Cache;

use App\Models\Banner;
use App\Models\BannerFieldMapping;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use Illuminate\Support\Facades\Cache;

class BannerCacheManager
{
    public const DEFAULT_TTL = 86400; // 24 hours

    /**
     * Cache and retrieve rendered HTML output.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $versionOrValues
     * @param array $options
     * @param callable $renderCallback
     * @return string
     */
    public function rememberRender(
        BannerTemplate $template,
        BannerVersion|array|null $versionOrValues,
        array $options,
        callable $renderCallback
    ): string {
        if (!config('banner_engine.caching.enabled', true)) {
            return $renderCallback();
        }

        $cacheKey = $this->generateRenderCacheKey($template, $versionOrValues, $options);
        $ttl = config('banner_engine.caching.ttl', self::DEFAULT_TTL);

        return Cache::remember($cacheKey, $ttl, $renderCallback);
    }

    /**
     * Generate unique cache key for a banner render.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $versionOrValues
     * @param array $options
     * @return string
     */
    public function generateRenderCacheKey(
        BannerTemplate $template,
        BannerVersion|array|null $versionOrValues,
        array $options = []
    ): string {
        $versionId = $versionOrValues instanceof BannerVersion ? $versionOrValues->id : 'dynamic';
        $valuesHash = is_array($versionOrValues) ? substr(hash('sha256', json_encode($versionOrValues)), 0, 12) : 'v';
        $viewport = $options['viewport'] ?? 'desktop';
        $updatedAt = $template->updated_at ? $template->updated_at->timestamp : 0;

        return "banner_engine:render:t{$template->id}:v{$versionId}:{$valuesHash}:{$viewport}:{$updatedAt}";
    }

    /**
     * Invalidate all cached renderings for a template.
     *
     * @param int|BannerTemplate $template
     * @return void
     */
    public function invalidateTemplate(int|BannerTemplate $template): void
    {
        $templateId = $template instanceof BannerTemplate ? $template->id : $template;

        // Invalidate known render cache keys
        foreach (['desktop', 'tablet', 'mobile'] as $vp) {
            Cache::forget("banner_engine:render:t{$templateId}:dynamic:v:{$vp}:0");
        }

        // Increment template touch timestamp to invalidate versioned keys
        if ($template instanceof BannerTemplate) {
            $template->touch();
        } else {
            BannerTemplate::where('id', $templateId)->update(['updated_at' => now()]);
        }
    }

    /**
     * Invalidate all banners mapped to a specific product.
     *
     * @param int $productId
     * @return void
     */
    public function invalidateProduct(int $productId): void
    {
        $versionIds = BannerFieldMapping::where('product_id', $productId)
            ->pluck('banner_version_id')
            ->filter()
            ->unique();

        $templates = BannerTemplate::whereHas('versions', function ($q) use ($versionIds) {
            $q->whereIn('id', $versionIds);
        })->get();

        foreach ($templates as $template) {
            $this->invalidateTemplate($template);
        }
    }
}
