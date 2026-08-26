<?php

namespace App\Services\BannerEngine\Versioning;

use App\Models\Banner;
use App\Models\BannerPublication;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\User;
use App\Services\BannerEngine\Cache\BannerCacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BannerVersionManager
{
    protected BannerCacheManager $cacheManager;

    public function __construct(?BannerCacheManager $cacheManager = null)
    {
        $this->cacheManager = $cacheManager ?? new BannerCacheManager();
    }

    /**
     * Create a new draft version for a banner.
     *
     * @param Banner $banner
     * @param array $fieldValues
     * @param string|null $summary
     * @param User|int|null $user
     * @return BannerVersion
     */
    public function createDraft(
        Banner $banner,
        array $fieldValues,
        ?string $summary = null,
        User|int|null $user = null
    ): BannerVersion {
        $nextVersion = ($banner->versions()->max('version_number') ?? 0) + 1;
        $userId = $user instanceof User ? $user->id : ($user ?? Auth::id());
        $template = $banner->template;

        return BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template ? $template->id : null,
            'version_number' => $nextVersion,
            'status' => BannerVersion::STATUS_DRAFT,
            'field_values' => $fieldValues,
            'template_snapshot' => [
                'dynamic_schema' => $template?->dynamic_schema ?? [],
                'raw_html_hash' => $template ? substr(hash('sha256', $template->raw_html), 0, 16) : null,
                'created_at' => now()->toIso8601String(),
            ],
            'change_summary' => $summary ?: "Draft created (v{$nextVersion})",
            'created_by' => $userId,
        ]);
    }

    /**
     * Publish a banner version.
     *
     * @param BannerVersion $version
     * @param User|int|null $user
     * @return BannerVersion
     */
    public function publishVersion(BannerVersion $version, User|int|null $user = null): BannerVersion
    {
        $banner = $version->banner;
        $userId = $user instanceof User ? $user->id : ($user ?? Auth::id());

        // 1. Archive previously published versions
        $banner->versions()
            ->where('status', BannerVersion::STATUS_PUBLISHED)
            ->where('id', '!=', $version->id)
            ->update(['status' => BannerVersion::STATUS_ARCHIVED]);

        // 2. Mark current version as published
        $version->update([
            'status' => BannerVersion::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // 3. Update Banner active version pointer
        $banner->update([
            'active_version_id' => $version->id,
            'current_template_id' => $version->template_id ?: $banner->current_template_id,
        ]);

        // 4. Record BannerPublication entry
        BannerPublication::where('banner_id', $banner->id)->update(['is_active' => false]);
        BannerPublication::create([
            'banner_id' => $banner->id,
            'version_id' => $version->id,
            'position' => $banner->position ?? 'home_hero',
            'is_active' => true,
        ]);

        // 5. Invalidate caches
        if ($banner->template) {
            $this->cacheManager->invalidateTemplate($banner->template);
        }

        return $version;
    }

    /**
     * Rollback a banner to a previous version snapshot.
     *
     * @param Banner $banner
     * @param BannerVersion|int $targetVersion
     * @param User|int|null $user
     * @return BannerVersion
     */
    public function rollbackToVersion(
        Banner $banner,
        BannerVersion|int $targetVersion,
        User|int|null $user = null
    ): BannerVersion {
        $version = $targetVersion instanceof BannerVersion
            ? $targetVersion
            : BannerVersion::where('banner_id', $banner->id)->findOrFail($targetVersion);

        // Create a new version with restored snapshot and publish it
        $nextVersionNumber = ($banner->versions()->max('version_number') ?? 0) + 1;
        $userId = $user instanceof User ? $user->id : ($user ?? Auth::id());

        $restoredVersion = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $version->template_id,
            'version_number' => $nextVersionNumber,
            'status' => BannerVersion::STATUS_DRAFT,
            'field_values' => $version->field_values,
            'template_snapshot' => $version->template_snapshot,
            'change_summary' => "Rollback to v{$version->version_number}",
            'created_by' => $userId,
        ]);

        return $this->publishVersion($restoredVersion, $userId);
    }

    /**
     * Check if a banner is currently scheduled and active.
     *
     * @param Banner $banner
     * @return bool
     */
    public function isBannerActiveNow(Banner $banner): bool
    {
        if (!$banner->is_active) {
            return false;
        }

        $now = now();

        if ($banner->starts_at && $now->lt($banner->starts_at)) {
            return false;
        }

        if ($banner->expires_at && $now->gt($banner->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get complete version history with publications.
     *
     * @param Banner $banner
     * @return Collection<int, BannerVersion>
     */
    public function getVersionHistory(Banner $banner): Collection
    {
        return $banner->versions()
            ->with(['creator', 'template', 'publications'])
            ->orderBy('version_number', 'desc')
            ->get();
    }
}
