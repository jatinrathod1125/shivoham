<?php

namespace App\Services\BannerEngine\Renderer;

use App\Models\BannerAsset;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\BannerEngineManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrowserRenderingEngine
{
    protected SandboxedRenderer $renderer;

    public function __construct(?SandboxedRenderer $renderer = null)
    {
        $this->renderer = $renderer ?? new SandboxedRenderer();
    }

    /**
     * Get list of supported viewports from config.
     *
     * @return array
     */
    public function getSupportedViewports(): array
    {
        return Config::get('banner_engine.viewports', [
            'desktop' => ['width' => 1440, 'height' => 600, 'label' => 'Desktop (1440px)'],
            'tablet' => ['width' => 768, 'height' => 500, 'label' => 'Tablet (768px)'],
            'mobile' => ['width' => 375, 'height' => 450, 'label' => 'Mobile (375px)'],
        ]);
    }

    /**
     * Prepare a fully instrumented sandboxed HTML document for headless browser or iframe inspection.
     *
     * @param BannerTemplate $template
     * @param string $viewport
     * @param array $options
     * @return string
     */
    public function prepareInstrumentedDocument(BannerTemplate $template, string $viewport = 'desktop', array $options = []): string
    {
        $viewports = $this->getSupportedViewports();
        $vpConfig = $viewports[$viewport] ?? $viewports['desktop'];

        $baseHtml = $this->renderer->render($template, $options['values'] ?? null);
        $inspectorJs = LayoutInspectorScript::getInspectorScript();

        // Inject viewport constraints and inspector script before </body>
        $injection = <<<HTML
    <!-- BannerEngine Layout Inspector Probe -->
    <script>
        {$inspectorJs}
    </script>
</body>
HTML;

        return str_replace('</body>', $injection, $baseHtml);
    }

    /**
     * Ingest visual inspection metrics from browser/worker and enrich template dynamic schema and viewports.
     *
     * @param BannerTemplate $template
     * @param string $viewport
     * @param array $metricsData
     * @return BannerTemplate
     */
    public function recordViewportMetrics(BannerTemplate $template, string $viewport, array $metricsData): BannerTemplate
    {
        $currentViewports = $template->viewports ?? [];
        $currentViewports[$viewport] = [
            'viewport_config' => $this->getSupportedViewports()[$viewport] ?? [],
            'measured_at' => now()->toIso8601String(),
            'elements_count' => $metricsData['elements_count'] ?? count($metricsData['elements'] ?? []),
            'elements' => $metricsData['elements'] ?? [],
        ];

        // Match visual bounding boxes back into dynamic schema
        $currentSchema = $template->dynamic_schema ?? ['elements' => []];
        $schemaElements = $currentSchema['elements'] ?? [];

        if (!empty($metricsData['elements'])) {
            $metricsByPath = [];
            foreach ($metricsData['elements'] as $elMetric) {
                if (!empty($elMetric['dom_path'])) {
                    $metricsByPath[$elMetric['dom_path']] = $elMetric;
                }
            }

            foreach ($schemaElements as &$schemaEl) {
                $path = $schemaEl['dom_path'] ?? '';
                if (isset($metricsByPath[$path])) {
                    $m = $metricsByPath[$path];
                    $schemaEl['viewports'][$viewport] = [
                        'bounding_box' => $m['bounding_box'] ?? null,
                        'computed_styles' => $m['computed_styles'] ?? null,
                        'is_visible' => $m['is_visible'] ?? true,
                        'is_overflowing_x' => $m['is_overflowing_x'] ?? false,
                        'is_overflowing_y' => $m['is_overflowing_y'] ?? false,
                        'prominence_score' => $m['prominence_score'] ?? 0,
                    ];
                }
            }
            unset($schemaEl);
        }

        $currentSchema['elements'] = $schemaElements;

        $template->update([
            'viewports' => $currentViewports,
            'dynamic_schema' => $currentSchema,
        ]);

        return $template->fresh();
    }

    /**
     * Store a screenshot captured for a specific viewport.
     *
     * @param BannerTemplate $template
     * @param string $viewport ('desktop', 'tablet', 'mobile')
     * @param string $imageData (binary or base64 data)
     * @param string $mimeType
     * @return BannerAsset
     */
    public function storeScreenshot(BannerTemplate $template, string $viewport, string $imageData, string $mimeType = 'image/png'): BannerAsset
    {
        // Decode if base64
        if (str_starts_with($imageData, 'data:image')) {
            $parts = explode(',', $imageData, 2);
            $imageData = base64_decode($parts[1] ?? '');
        }

        $disk = Config::get('banner_engine.storage_disk', 'public');
        $basePath = Config::get('banner_engine.storage_path', 'banner_engine');

        $filename = "screenshots/{$viewport}.png";
        $storagePath = "{$basePath}/{$template->id}/{$filename}";

        Storage::disk($disk)->put($storagePath, $imageData);
        $url = Storage::disk($disk)->url($storagePath);

        $asset = BannerAsset::updateOrCreate(
            [
                'template_id' => $template->id,
                'original_filename' => "screenshot_{$viewport}.png",
            ],
            [
                'stored_path' => $storagePath,
                'url' => $url,
                'mime_type' => $mimeType,
                'file_size' => strlen($imageData),
                'file_hash' => hash('sha256', $imageData),
                'asset_type' => BannerAsset::TYPE_IMAGE,
                'metadata' => [
                    'viewport' => $viewport,
                    'is_screenshot' => true,
                    'captured_at' => now()->toIso8601String(),
                ],
            ]
        );

        // Update render_metadata in template
        $renderMetadata = $template->render_metadata ?? [];
        $renderMetadata['screenshots'][$viewport] = [
            'asset_id' => $asset->id,
            'url' => $url,
            'captured_at' => now()->toIso8601String(),
        ];

        $template->update([
            'render_metadata' => $renderMetadata,
        ]);

        return $asset;
    }
}
