<?php

namespace App\Services\BannerEngine\Import;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Contracts\ImportServiceInterface;
use App\Services\BannerEngine\Sanitizer\HtmlSanitizer;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ZipImportService implements ImportServiceInterface
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(?HtmlSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? new HtmlSanitizer();
    }

    /**
     * Import a design package from a ZIP file.
     *
     * @param UploadedFile|string $file
     * @param array $options
     * @return BannerTemplate
     * @throws Exception
     */
    public function importZip(UploadedFile|string $file, array $options = []): BannerTemplate
    {
        $zipPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!file_exists($zipPath)) {
            throw new Exception("ZIP package not found at path: {$zipPath}");
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath);

        if ($openResult !== true) {
            throw new Exception("Failed to open ZIP archive. Error code: {$openResult}");
        }

        $tempExtractDir = storage_path('app/temp_banner_imports/' . Str::uuid());
        File::ensureDirectoryExists($tempExtractDir);

        try {
            $extractedFiles = $this->safelyExtractZip($zip, $tempExtractDir);
            $zip->close();

            // Normalize base directory if archive has a single top-level folder wrapper
            $effectiveBaseDir = $this->resolveEffectiveBaseDir($tempExtractDir);

            // Locate entry HTML
            $entryFileRelative = $this->findEntryHtmlFile($effectiveBaseDir);
            if (!$entryFileRelative) {
                throw new Exception('No valid HTML entry file (such as index.html) found in the ZIP archive.');
            }

            $rawHtmlContent = File::get($effectiveBaseDir . DIRECTORY_SEPARATOR . $entryFileRelative);
            $sanitizedHtml = $this->sanitizer->sanitizeHtml($rawHtmlContent);

            // Create preliminary BannerTemplate
            $templateName = $options['name'] ?? pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $file, PATHINFO_FILENAME);
            $bannerId = $options['banner_id'] ?? null;

            $template = BannerTemplate::create([
                'banner_id' => $bannerId,
                'name' => $templateName,
                'import_source' => BannerTemplate::SOURCE_ZIP,
                'entry_file' => $entryFileRelative,
                'raw_html' => $sanitizedHtml,
                'raw_css' => '',
                'raw_js' => '',
                'asset_manifest' => [],
                'dynamic_schema' => [],
                'viewports' => Config::get('banner_engine.viewports', []),
                'render_metadata' => [
                    'imported_at' => now()->toIso8601String(),
                    'source_filename' => $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file),
                ],
                'is_active' => true,
            ]);

            // If banner exists and has no template, link it
            if ($bannerId) {
                $banner = Banner::find($bannerId);
                if ($banner && !$banner->current_template_id) {
                    $banner->update([
                        'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
                        'current_template_id' => $template->id,
                    ]);
                }
            }

            // Process and store all non-HTML assets and gather stylesheets/scripts
            $manifest = [];
            $cssContents = [];
            $jsContents = [];
            $urlReplacements = [];

            $allFiles = File::allFiles($effectiveBaseDir);
            foreach ($allFiles as $fileItem) {
                $relPath = str_replace('\\', '/', substr($fileItem->getPathname(), strlen($effectiveBaseDir) + 1));

                // Skip macOS and OS junk
                if (str_starts_with($relPath, '__MACOSX') || str_contains($relPath, '.DS_Store') || str_contains($relPath, 'Thumbs.db')) {
                    continue;
                }

                // Check forbidden executable files
                if (AssetManager::isForbiddenExtension($relPath)) {
                    continue;
                }

                $content = File::get($fileItem->getPathname());
                $asset = AssetManager::storeAsset($template, $relPath, $content);

                $manifest[$relPath] = [
                    'asset_id' => $asset->id,
                    'original_path' => $relPath,
                    'stored_path' => $asset->stored_path,
                    'url' => $asset->url,
                    'mime_type' => $asset->mime_type,
                    'file_size' => $asset->file_size,
                    'asset_type' => $asset->asset_type,
                ];

                // Register URL replacements for HTML/CSS references
                $urlReplacements[$relPath] = $asset->url;
                $urlReplacements['./' . $relPath] = $asset->url;
                $urlReplacements['/' . $relPath] = $asset->url;

                if ($asset->asset_type === \App\Models\BannerAsset::TYPE_STYLESHEET) {
                    $cssContents[] = $this->sanitizer->sanitizeCss($content);
                } elseif ($asset->asset_type === \App\Models\BannerAsset::TYPE_SCRIPT) {
                    $jsContents[] = $content;
                }
            }

            // Rewrite relative asset paths in HTML to resolved storage URLs
            $rewrittenHtml = $this->rewriteAssetUrls($sanitizedHtml, $urlReplacements);

            // Also rewrite relative URLs in collected CSS
            $combinedCss = implode("\n\n/* --- Next Stylesheet --- */\n\n", $cssContents);
            $rewrittenCss = $this->rewriteAssetUrls($combinedCss, $urlReplacements);
            $combinedJs = implode("\n\n;\n\n", $jsContents);

            $template->update([
                'raw_html' => $rewrittenHtml,
                'raw_css' => $rewrittenCss,
                'raw_js' => $combinedJs,
                'asset_manifest' => $manifest,
            ]);

            return $template->fresh();
        } finally {
            // Clean up temporary extraction folder
            if (File::exists($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    /**
     * Import raw HTML, CSS, and JS snippets.
     *
     * @param string $html
     * @param string|null $css
     * @param string|null $js
     * @param array $options
     * @return BannerTemplate
     */
    public function importRawCode(string $html, ?string $css = null, ?string $js = null, array $options = []): BannerTemplate
    {
        $htmlService = new HtmlImportService($this->sanitizer);
        return $htmlService->importRawCode($html, $css, $js, $options);
    }

    public function importPsd(UploadedFile|string $file, array $options = []): BannerTemplate
    {
        $psdService = new PsdImportService();
        return $psdService->importPsd($file, $options);
    }

    /**
     * Safely extract ZIP archive contents while preventing ZipSlip directory traversal.
     *
     * @param ZipArchive $zip
     * @param string $extractTo
     * @return array
     * @throws Exception
     */
    protected function safelyExtractZip(ZipArchive $zip, string $extractTo): array
    {
        $maxFiles = Config::get('banner_engine.limits.max_extracted_files', 200);
        $maxZipKb = Config::get('banner_engine.limits.max_zip_size_kb', 51200);
        $totalBytes = 0;
        $extractedFiles = [];

        $realBase = realpath($extractTo);

        if ($zip->numFiles > $maxFiles) {
            throw new Exception("Archive contains {$zip->numFiles} files, exceeding the maximum allowed limit of {$maxFiles}.");
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name'];

            // Skip directory entries
            if (str_ends_with($filename, '/')) {
                continue;
            }

            // Reject forbidden executable extensions
            if (AssetManager::isForbiddenExtension($filename)) {
                continue;
            }

            $totalBytes += $stat['size'];
            if ($totalBytes > ($maxZipKb * 1024)) {
                throw new Exception("Total extracted archive size exceeds allowed maximum quota of {$maxZipKb} KB.");
            }

            $targetPath = $extractTo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filename);
            $targetDir = dirname($targetPath);

            File::ensureDirectoryExists($targetDir);

            // Extract single file stream
            $fp = $zip->getStream($filename);
            if (!$fp) {
                continue;
            }

            $targetFp = fopen($targetPath, 'wb');
            if ($targetFp) {
                stream_copy_to_stream($fp, $targetFp);
                fclose($targetFp);
            }
            fclose($fp);

            // Verify ZipSlip realpath constraint
            $realTarget = realpath($targetPath);
            if (!$realTarget || !str_starts_with($realTarget, $realBase)) {
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }
                throw new Exception("Malicious path traversal detected in ZIP entry: {$filename}");
            }

            $extractedFiles[] = $targetPath;
        }

        return $extractedFiles;
    }

    /**
     * Resolve effective root directory if archive is wrapped inside a single folder.
     *
     * @param string $extractDir
     * @return string
     */
    protected function resolveEffectiveBaseDir(string $extractDir): string
    {
        $files = File::files($extractDir);
        $dirs = File::directories($extractDir);

        if (count($files) === 0 && count($dirs) === 1) {
            return $dirs[0];
        }

        return $extractDir;
    }

    /**
     * Locate the primary entry HTML file in directory.
     *
     * @param string $baseDir
     * @return string|null
     */
    protected function findEntryHtmlFile(string $baseDir): ?string
    {
        $priorityFiles = ['index.html', 'index.htm', 'banner.html', 'hero.html', 'template.html', 'default.html'];

        foreach ($priorityFiles as $file) {
            if (File::exists($baseDir . DIRECTORY_SEPARATOR . $file)) {
                return $file;
            }
        }

        // Look for any .html in root
        $htmlFiles = File::glob($baseDir . DIRECTORY_SEPARATOR . '*.html');
        if (!empty($htmlFiles)) {
            return basename($htmlFiles[0]);
        }

        return null;
    }

    /**
     * Rewrite relative paths to storage URLs.
     *
     * @param string $content
     * @param array $replacements
     * @return string
     */
    protected function rewriteAssetUrls(string $content, array $replacements): string
    {
        if (empty($content) || empty($replacements)) {
            return $content;
        }

        // Sort keys by length descending to prevent sub-string collision
        uksort($replacements, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($replacements as $relative => $url) {
            // Replace in src="...", href="...", url('...'), url("...")
            $quoted = preg_quote($relative, '/');
            $content = preg_replace('/([\'"])\s*' . $quoted . '\s*([\'"])/i', '$1' . $url . '$2', $content);
            $content = preg_replace('/url\(\s*[\'"]?\s*' . $quoted . '\s*[\'"]?\s*\)/i', "url('{$url}')", $content);
        }

        return $content;
    }
}
