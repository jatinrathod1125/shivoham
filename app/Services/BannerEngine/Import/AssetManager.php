<?php

namespace App\Services\BannerEngine\Import;

use App\Models\BannerAsset;
use App\Models\BannerTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetManager
{
    /**
     * Determine the asset category type from extension and mime type.
     *
     * @param string $filename
     * @param string $mimeType
     * @return string
     */
    public static function detectAssetType(string $filename, string $mimeType): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'bmp', 'ico', 'avif']) || str_starts_with($mimeType, 'image/')) {
            return BannerAsset::TYPE_IMAGE;
        }

        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov']) || str_starts_with($mimeType, 'video/')) {
            return BannerAsset::TYPE_VIDEO;
        }

        if (in_array($ext, ['woff', 'woff2', 'ttf', 'otf', 'eot']) || str_contains($mimeType, 'font')) {
            return BannerAsset::TYPE_FONT;
        }

        if ($ext === 'css' || $mimeType === 'text/css') {
            return BannerAsset::TYPE_STYLESHEET;
        }

        if (in_array($ext, ['js', 'mjs']) || str_contains($mimeType, 'javascript')) {
            return BannerAsset::TYPE_SCRIPT;
        }

        if (in_array($ext, ['gltf', 'glb', 'obj', 'fbx', 'usdz', 'bin'])) {
            return BannerAsset::TYPE_MODEL;
        }

        return BannerAsset::TYPE_OTHER;
    }

    /**
     * Check if a file extension is strictly forbidden for security reasons.
     *
     * @param string $filename
     * @return bool
     */
    public static function isForbiddenExtension(string $filename): bool
    {
        $forbidden = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'inc',
            'exe', 'dll', 'so', 'bat', 'cmd', 'sh', 'bash', 'zsh',
            'vbs', 'ps1', 'cgi', 'pl', 'py', 'rb', 'asp', 'aspx',
            'htaccess', 'htpasswd', 'env', 'config',
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $forbidden, true);
    }

    /**
     * Store an asset for a template and record in database.
     *
     * @param BannerTemplate $template
     * @param string $relativePath
     * @param string $binaryContent
     * @param string|null $mimeType
     * @return BannerAsset
     */
    public static function storeAsset(BannerTemplate $template, string $relativePath, string $binaryContent, ?string $mimeType = null): BannerAsset
    {
        $disk = Config::get('banner_engine.storage_disk', 'public');
        $basePath = Config::get('banner_engine.storage_path', 'banner_engine');

        $cleanRelativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $storagePath = "{$basePath}/{$template->id}/{$cleanRelativePath}";

        // Store file on configured disk
        Storage::disk($disk)->put($storagePath, $binaryContent);

        $url = Storage::disk($disk)->url($storagePath);
        $fileSize = strlen($binaryContent);
        $fileHash = hash('sha256', $binaryContent);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $mimeType ?: (finfo_buffer($finfo, $binaryContent) ?: 'application/octet-stream');
        finfo_close($finfo);

        $assetType = self::detectAssetType($cleanRelativePath, $detectedMime);

        return BannerAsset::create([
            'template_id' => $template->id,
            'original_filename' => $cleanRelativePath,
            'stored_path' => $storagePath,
            'url' => $url,
            'mime_type' => $detectedMime,
            'file_size' => $fileSize,
            'file_hash' => $fileHash,
            'asset_type' => $assetType,
            'metadata' => [
                'disk' => $disk,
                'extension' => pathinfo($cleanRelativePath, PATHINFO_EXTENSION),
            ],
        ]);
    }
}
