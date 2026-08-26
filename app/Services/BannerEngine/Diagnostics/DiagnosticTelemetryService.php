<?php

namespace App\Services\BannerEngine\Diagnostics;

use App\Models\BannerTemplate;
use Illuminate\Support\Facades\Log;

class DiagnosticTelemetryService
{
    protected const LOG_CHANNEL = 'banner_engine';

    /**
     * Record a structured diagnostic telemetry event.
     *
     * @param string $subsystem
     * @param string $severity (info, warning, error, critical)
     * @param string $message
     * @param array $context
     * @return array
     */
    public function record(string $subsystem, string $severity, string $message, array $context = []): array
    {
        $payload = [
            'timestamp' => now()->toIso8601String(),
            'subsystem' => $subsystem,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
        ];

        try {
            Log::channel(config('banner_engine.logging.channel', 'daily'))->log($severity, "[BannerEngine:{$subsystem}] {$message}", $context);
        } catch (\Throwable $e) {
            Log::log($severity, "[BannerEngine:{$subsystem}] {$message}", $context);
        }

        return $payload;
    }

    /**
     * Provide a safe inline SVG placeholder for missing or 404 images.
     *
     * @param string $label
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getSafePlaceholderImage(string $label = 'Media Asset', int $width = 600, int $height = 400): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $innerWidth = max(10, $width - 4);
        $innerHeight = max(10, $height - 4);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" fill="none">
    <rect width="{$width}" height="{$height}" fill="#F1F5F9"/>
    <rect x="2" y="2" width="{$innerWidth}" height="{$innerHeight}" stroke="#CBD5E1" stroke-width="2" stroke-dasharray="8 8"/>
    <text x="50%" y="48%" dominant-baseline="middle" text-anchor="middle" font-family="system-ui, sans-serif" font-size="16" font-weight="600" fill="#64748B">{$safeLabel}</text>
    <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle" font-family="system-ui, sans-serif" font-size="12" fill="#94A3B8">Universal AI Banner Engine</text>
</svg>
SVG;
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * Repair severely malformed HTML markup and return clean DOM structure.
     *
     * @param string $rawHtml
     * @return string
     */
    public function repairMalformedHtml(string $rawHtml): string
    {
        if (empty(trim($rawHtml))) {
            return '<div class="banner-empty"></div>';
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $rawHtml . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            return $rawHtml;
        }

        $repaired = '';
        foreach ($root->childNodes as $child) {
            $repaired .= $dom->saveHTML($child);
        }

        return trim($repaired);
    }
}
