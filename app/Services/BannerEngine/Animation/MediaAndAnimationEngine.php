<?php

namespace App\Services\BannerEngine\Animation;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\CssAnalyzer;
use App\Services\BannerEngine\Analyzer\DomAnalyzer;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use DOMDocument;
use DOMElement;
use DOMXPath;

class MediaAndAnimationEngine
{
    protected DomAnalyzer $domAnalyzer;
    protected CssAnalyzer $cssAnalyzer;
    protected DynamicInjector $injector;

    public function __construct(
        ?DomAnalyzer $domAnalyzer = null,
        ?CssAnalyzer $cssAnalyzer = null,
        ?DynamicInjector $injector = null
    ) {
        $this->domAnalyzer = $domAnalyzer ?? new DomAnalyzer();
        $this->cssAnalyzer = $cssAnalyzer ?? new CssAnalyzer();
        $this->injector = $injector ?? new DynamicInjector();
    }

    /**
     * Inspect and categorize rich media, animations, Lottie players, and 3D scenes.
     *
     * @param BannerTemplate $template
     * @return array
     */
    public function inspectRichMedia(BannerTemplate $template): array
    {
        $domData = $this->domAnalyzer->analyzeDom($template->raw_html);
        $cssData = $this->cssAnalyzer->analyzeCss($template->raw_css ?? '');
        $js = $template->raw_js ?? '';

        $videos = [];
        $lottiePlayers = [];
        $canvasScenes = [];
        $animationFrameworks = [];

        // 1. Detect Video Elements
        foreach ($domData['elements'] as $el) {
            if ($el['tag'] === 'video') {
                $videos[] = [
                    'dom_path' => $el['dom_path'],
                    'src' => $el['src'],
                    'has_source_tags' => !empty($el['attributes']['src']),
                    'autoplay' => isset($el['attributes']['autoplay']),
                    'loop' => isset($el['attributes']['loop']),
                    'muted' => isset($el['attributes']['muted']),
                ];
            }

            // 2. Detect Lottie Players
            if (
                $el['tag'] === 'lottie-player' ||
                !empty($el['attributes']['data-lottie']) ||
                !empty($el['attributes']['data-animation-path'])
            ) {
                $lottiePlayers[] = [
                    'dom_path' => $el['dom_path'],
                    'tag' => $el['tag'],
                    'path' => $el['attributes']['data-lottie'] ?? ($el['attributes']['src'] ?? ($el['attributes']['data-animation-path'] ?? '')),
                ];
            }

            // 3. Detect Canvas / 3D WebGL
            if ($el['tag'] === 'canvas') {
                $canvasScenes[] = [
                    'dom_path' => $el['dom_path'],
                    'id' => $el['id'],
                    'is_webgl' => str_contains($js, 'WebGLRenderer') || str_contains($js, 'three') || str_contains($js, 'BABYLON'),
                ];
            }
        }

        // 4. Detect Animation Frameworks in JS / CSS
        if (str_contains($js, 'gsap') || str_contains($js, 'TimelineMax') || str_contains($js, 'TweenMax')) {
            $animationFrameworks[] = 'GSAP (GreenSock Animation Platform)';
        }
        if (str_contains($js, 'anime') || str_contains($js, 'anime.js')) {
            $animationFrameworks[] = 'Anime.js';
        }
        if (str_contains($js, 'lottie') || !empty($lottiePlayers)) {
            $animationFrameworks[] = 'Lottie (Bodymovin)';
        }
        if (str_contains($js, 'THREE') || str_contains($js, 'three.js') || !empty($canvasScenes)) {
            $animationFrameworks[] = 'Three.js / WebGL 3D Canvas';
        }

        return [
            'has_animations' => !empty($cssData['keyframes']) || !empty($animationFrameworks),
            'keyframes_count' => count($cssData['keyframes']),
            'keyframes' => array_column($cssData['keyframes'], 'name'),
            'animation_frameworks' => array_unique($animationFrameworks),
            'videos_count' => count($videos),
            'videos' => $videos,
            'lottie_count' => count($lottiePlayers),
            'lottie_players' => $lottiePlayers,
            'canvas_scenes_count' => count($canvasScenes),
            'canvas_scenes' => $canvasScenes,
        ];
    }

    /**
     * Replace dynamic video and media assets while preserving animation timing and video attributes.
     *
     * @param string $html
     * @param BannerTemplate $template
     * @param array $mediaOverrides
     * @return string
     */
    public function applyMediaOverrides(string $html, BannerTemplate $template, array $mediaOverrides): string
    {
        return $this->injector->inject($html, $template, $mediaOverrides);
    }
}
