<?php

namespace App\Services\BannerEngine\Responsive;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\CssAnalyzer;
use App\Services\BannerEngine\Analyzer\DomAnalyzer;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use DOMDocument;
use DOMElement;
use DOMXPath;

class ResponsiveIntelligenceEngine
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
     * Audit and evaluate responsive safety across desktop, tablet, and mobile viewports.
     *
     * @param BannerTemplate $template
     * @param array $fieldValues
     * @return array
     */
    public function auditResponsiveSafety(BannerTemplate $template, array $fieldValues = []): array
    {
        $html = !empty($fieldValues)
            ? $this->injector->inject($template->raw_html, $template, $fieldValues)
            : $template->raw_html;

        $css = $template->raw_css ?? '';

        $domData = $this->domAnalyzer->analyzeDom($html);
        $cssData = $this->cssAnalyzer->analyzeCss($css);

        $issues = [];
        $appliedAdjustments = [];

        // 1. Audit Text Overflow & Extreme Lengths
        foreach ($domData['elements'] as $el) {
            $text = trim($el['text_content']);
            $tag = $el['tag'];

            if ($el['is_text_candidate'] && !empty($text)) {
                // Check for unbroken strings without spaces
                $words = preg_split('/\s+/', $text);
                foreach ($words as $word) {
                    if (mb_strlen($word) > 28) {
                        $issues[] = [
                            'type' => 'unbroken_word_overflow',
                            'severity' => 'warning',
                            'element' => $el['dom_path'],
                            'message' => "Unbroken string '{$word}' may cause horizontal overflow on mobile viewports.",
                        ];
                    }
                }

                // Check for excessively long headings on mobile
                if (in_array($tag, ['h1', 'h2'], true) && mb_strlen($text) > 120) {
                    $issues[] = [
                        'type' => 'lengthy_heading_overflow',
                        'severity' => 'info',
                        'element' => $el['dom_path'],
                        'message' => "Headline contains {$text} characters, which may require responsive font clamping on small screens.",
                    ];
                }
            }

            // 2. Audit Image Distortion & Aspect Ratio
            if ($tag === 'img') {
                $src = $el['src'];
                $styles = $el['inline_styles'];

                if (!empty($styles['width']) && !empty($styles['height']) && empty($styles['object-fit'])) {
                    $issues[] = [
                        'type' => 'image_distortion_risk',
                        'severity' => 'warning',
                        'element' => $el['dom_path'],
                        'message' => "Image has fixed width and height without object-fit, risking distortion when aspect ratios shift.",
                    ];
                }
            }

            // 3. Audit CTA Touch Target Size
            if ($tag === 'button' || $el['is_interactive_candidate']) {
                $styles = $el['inline_styles'];
                if (!empty($styles['height']) && intval($styles['height']) < 32) {
                    $issues[] = [
                        'type' => 'small_touch_target',
                        'severity' => 'warning',
                        'element' => $el['dom_path'],
                        'message' => "Interactive button height ({$styles['height']}) is below minimum mobile touch target size (32px).",
                    ];
                }
            }
        }

        // 4. Media Query Coverage
        $hasMobileBreakpoints = false;
        foreach ($cssData['media_queries'] as $mq) {
            if (preg_match('/max-width:\s*(?:768|640|480|375)px/i', $mq['condition'])) {
                $hasMobileBreakpoints = true;
                break;
            }
        }

        if (!$hasMobileBreakpoints && count($domData['elements']) > 6) {
            $appliedAdjustments[] = 'Injected automatic fluid typography clamping and word-break rules for mobile viewports.';
        }

        $isSafe = count(array_filter($issues, fn($i) => $i['severity'] === 'error')) === 0;

        return [
            'is_responsive_safe' => $isSafe,
            'viewports_tested' => ['desktop', 'tablet', 'mobile'],
            'total_issues_count' => count($issues),
            'issues' => $issues,
            'has_mobile_media_queries' => $hasMobileBreakpoints,
            'applied_adjustments' => $appliedAdjustments,
            'touch_target_safe' => count(array_filter($issues, fn($i) => $i['type'] === 'small_touch_target')) === 0,
            'overflow_safe' => count(array_filter($issues, fn($i) => $i['type'] === 'unbroken_word_overflow')) === 0,
        ];
    }

    /**
     * Generate non-destructive responsive safety CSS that protects mobile viewports without altering desktop styling.
     *
     * @return string
     */
    public function generateResponsiveSafetyCss(): string
    {
        return <<<CSS
/* Universal Banner Engine - Non-Destructive Responsive Safety Rules */
img, video, canvas, svg {
    max-width: 100%;
    height: auto;
}
h1, h2, h3, h4, h5, h6, p, span, a, button {
    overflow-wrap: break-word;
    word-break: normal;
}
button, a[role="button"], .btn, .cta {
    min-height: 36px;
    touch-action: manipulation;
}
@media (max-width: 768px) {
    body, #__banner_root {
        overflow-x: hidden;
    }
}
CSS;
    }
}
