<?php

namespace App\Services\BannerEngine\Renderer;

use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Services\BannerEngine\BannerEngineManager;
use App\Services\BannerEngine\Contracts\RendererInterface;
use Illuminate\Support\Facades\Config;

class SandboxedRenderer implements RendererInterface
{
    /**
     * Render sandboxed HTML document with performance caching.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $values
     * @param array $options
     * @return string
     */
    public function renderCached(BannerTemplate $template, BannerVersion|array|null $values = null, array $options = []): string
    {
        $cache = new \App\Services\BannerEngine\Cache\BannerCacheManager();
        return $cache->rememberRender($template, $values, $options, function () use ($template, $values, $options) {
            return $this->render($template, $values, $options);
        });
    }

    /**
     * Render sandboxed standalone HTML document.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $values
     * @param array $options
     * @return string
     */
    public function render(BannerTemplate $template, BannerVersion|array|null $values = null, array $options = []): string
    {
        if (!empty($options['cached'])) {
            return $this->renderCached($template, $values, $options);
        }
        $cspHeader = BannerEngineManager::getCspHeader();
        $title = htmlspecialchars($template->name, ENT_QUOTES, 'UTF-8');

        $htmlContent = $template->raw_html;
        $cssContent = $template->raw_css ?? '';
        $jsContent = $template->raw_js ?? '';

        // If field value overrides or version mappings are supplied, resolve and apply them
        $resolver = new \App\Services\BannerEngine\Catalog\ProductBindingResolver();
        $resolvedValues = $resolver->resolveFieldValues($template, $values);

        if (!empty($resolvedValues)) {
            $htmlContent = $this->applyFieldValues($htmlContent, $template, $resolvedValues);
        }

        // Generate sandboxed HTML document
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="{$cspHeader}">
    <title>{$title}</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        /* Template Custom CSS */
        {$cssContent}

        /* Non-Destructive Responsive Safety Rules */
        img, video, canvas, svg {
            max-width: 100%;
        }
        h1, h2, h3, h4, h5, h6, p, span, a, button {
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    {$htmlContent}

    <!-- Template Custom Script Execution -->
    <script>
        (function() {
            try {
                {$jsContent}
            } catch (err) {
                console.warn('[BannerEngine Sandbox] Script execution notice:', err);
            }

            // Communicate height to parent container
            function notifyHeight() {
                try {
                    var h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, 550);
                    window.parent.postMessage({ type: 'banner-resize', height: h }, '*');
                } catch(e) {}
            }
            window.addEventListener('load', notifyHeight);
            window.addEventListener('resize', notifyHeight);
            setTimeout(notifyHeight, 300);
            setTimeout(notifyHeight, 1000);
        })();
    </script>
</body>
</html>
HTML;
    }

    /**
     * Render an iframe embedding tag for Blade views with full sandbox security attributes.
     *
     * @param BannerTemplate $template
     * @param array $options
     * @return string
     */
    public function renderIframeTag(BannerTemplate $template, array $options = []): string
    {
        $id = $options['id'] ?? 'banner-sandbox-iframe-' . $template->id;
        $class = $options['class'] ?? 'w-full h-full border-0 rounded-xl overflow-hidden';
        $viewport = $options['viewport'] ?? 'desktop';

        $width = match ($viewport) {
            'mobile' => '375px',
            'tablet' => '768px',
            default => '100%',
        };

        $height = $options['height'] ?? '500px';

        $srcDoc = htmlspecialchars($this->render($template, $options['values'] ?? null), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<iframe
    id="{$id}"
    class="{$class}"
    style="width: {$width}; height: {$height}; transition: width 0.3s ease;"
    sandbox="allow-scripts allow-same-origin"
    srcdoc="{$srcDoc}"
></iframe>
HTML;
    }

    /**
     * Apply field value substitutions to the HTML string using DynamicInjector.
     *
     * @param string $html
     * @param BannerTemplate $template
     * @param array $fieldValues
     * @return string
     */
    protected function applyFieldValues(string $html, BannerTemplate $template, array $fieldValues): string
    {
        $injector = new \App\Services\BannerEngine\FieldEngine\DynamicInjector();
        return $injector->inject($html, $template, $fieldValues);
    }
}
