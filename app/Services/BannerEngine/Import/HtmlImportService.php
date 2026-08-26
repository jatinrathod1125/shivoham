<?php

namespace App\Services\BannerEngine\Import;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\Contracts\ImportServiceInterface;
use App\Services\BannerEngine\Sanitizer\HtmlSanitizer;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

class HtmlImportService implements ImportServiceInterface
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(?HtmlSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? new HtmlSanitizer();
    }

    public function importPsd(UploadedFile|string $file, array $options = []): BannerTemplate
    {
        $psdService = new PsdImportService();
        return $psdService->importPsd($file, $options);
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
        $extractedStyles = [];
        $extractedScripts = [];

        // Parse embedded <style> and <script> tags from HTML if present
        $cleanHtml = $this->extractAndStripInlineAssets($html, $extractedStyles, $extractedScripts);

        $sanitizedHtml = $this->sanitizer->sanitizeHtml($cleanHtml);

        // Combine provided CSS and extracted <style> blocks
        $allCss = ($css ? $this->sanitizer->sanitizeCss($css) : '');
        if (!empty($extractedStyles)) {
            $allCss .= ($allCss ? "\n\n" : '') . implode("\n\n", array_map([$this->sanitizer, 'sanitizeCss'], $extractedStyles));
        }

        // Combine provided JS and extracted <script> blocks
        $allJs = ($js ?? '');
        if (!empty($extractedScripts)) {
            $allJs .= ($allJs ? "\n\n;\n\n" : '') . implode("\n\n;\n\n", $extractedScripts);
        }

        $templateName = $options['name'] ?? 'Imported HTML Banner';
        $bannerId = $options['banner_id'] ?? null;

        $template = BannerTemplate::create([
            'banner_id' => $bannerId,
            'name' => $templateName,
            'import_source' => BannerTemplate::SOURCE_HTML,
            'entry_file' => 'inline.html',
            'raw_html' => $sanitizedHtml,
            'raw_css' => $allCss,
            'raw_js' => $allJs,
            'asset_manifest' => [],
            'dynamic_schema' => [],
            'viewports' => Config::get('banner_engine.viewports', []),
            'render_metadata' => [
                'imported_at' => now()->toIso8601String(),
                'mode' => 'raw_code',
            ],
            'is_active' => true,
        ]);

        if ($bannerId) {
            $banner = Banner::find($bannerId);
            if ($banner && !$banner->current_template_id) {
                $banner->update([
                    'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
                    'current_template_id' => $template->id,
                ]);
            }
        }

        return $template;
    }

    /**
     * Import a design package from a ZIP file (delegated to ZipImportService).
     *
     * @param UploadedFile|string $file
     * @param array $options
     * @return BannerTemplate
     */
    public function importZip(UploadedFile|string $file, array $options = []): BannerTemplate
    {
        $importer = new ZipImportService($this->sanitizer);
        return $importer->importZip($file, $options);
    }

    /**
     * Extract <style> and <script> contents from HTML and remove them from markup.
     *
     * @param string $html
     * @param array $extractedStyles
     * @param array $extractedScripts
     * @return string
     */
    protected function extractAndStripInlineAssets(string $html, array &$extractedStyles, array &$extractedScripts): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument();
        $previousLibxmlUseErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . '<div id="__parser_root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlUseErrors);

        $xpath = new DOMXPath($dom);

        // Extract style tags
        $styles = $xpath->query('//style');
        if ($styles) {
            foreach ($styles as $style) {
                $extractedStyles[] = $style->nodeValue;
                $style->parentNode?->removeChild($style);
            }
        }

        // Extract script tags
        $scripts = $xpath->query('//script');
        if ($scripts) {
            foreach ($scripts as $script) {
                $extractedScripts[] = $script->nodeValue;
                $script->parentNode?->removeChild($script);
            }
        }

        $root = $dom->getElementById('__parser_root');
        if (!$root) {
            return $html;
        }

        $cleanHtml = '';
        foreach ($root->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }

        return trim($cleanHtml);
    }
}
