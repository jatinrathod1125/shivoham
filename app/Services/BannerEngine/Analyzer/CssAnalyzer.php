<?php

namespace App\Services\BannerEngine\Analyzer;

class CssAnalyzer
{
    /**
     * Analyze raw CSS and extract selectors, media queries, animations, and typography rules.
     *
     * @param string $css
     * @return array
     */
    public function analyzeCss(string $css): array
    {
        if (trim($css) === '') {
            return [
                'rules' => [],
                'media_queries' => [],
                'keyframes' => [],
                'font_faces' => [],
                'custom_properties' => [],
                'total_rules' => 0,
            ];
        }

        // Clean comments
        $cleanCss = preg_replace('/\/\*.*?\*\//s', '', $css);

        $mediaQueries = $this->extractMediaQueries($cleanCss);
        $keyframes = $this->extractKeyframes($cleanCss);
        $fontFaces = $this->extractFontFaces($cleanCss);
        $customProperties = $this->extractCustomProperties($cleanCss);
        $rules = $this->extractStandardRules($cleanCss);

        return [
            'rules' => $rules,
            'media_queries' => $mediaQueries,
            'keyframes' => $keyframes,
            'font_faces' => $fontFaces,
            'custom_properties' => $customProperties,
            'total_rules' => count($rules),
        ];
    }

    /**
     * Extract @media queries and their encapsulated rules.
     *
     * @param string $css
     * @return array
     */
    protected function extractMediaQueries(string $css): array
    {
        $mediaQueries = [];
        preg_match_all('/@media\s*([^{]+)\{((?:[^{}]+|\{[^{}]*\})*)\}/is', $css, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $condition = trim($match[1]);
            $innerCss = trim($match[2]);
            $innerRules = $this->extractStandardRules($innerCss);

            // Parse breakpoint hints (max-width, min-width)
            $breakpoint = null;
            if (preg_match('/(?:max-width|min-width)\s*:\s*(\d+(?:px|em|rem))/i', $condition, $bpMatch)) {
                $breakpoint = $bpMatch[1];
            }

            $mediaQueries[] = [
                'condition' => $condition,
                'breakpoint' => $breakpoint,
                'rules' => $innerRules,
                'rule_count' => count($innerRules),
            ];
        }

        return $mediaQueries;
    }

    /**
     * Extract @keyframes animations.
     *
     * @param string $css
     * @return array
     */
    protected function extractKeyframes(string $css): array
    {
        $keyframes = [];
        preg_match_all('/@(keyframes|-webkit-keyframes)\s+([a-zA-Z0-9_-]+)\s*\{((?:[^{}]+|\{[^{}]*\})*)\}/is', $css, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = trim($match[2]);
            $stepsRaw = trim($match[3]);

            $keyframes[] = [
                'name' => $name,
                'raw' => $stepsRaw,
            ];
        }

        return $keyframes;
    }

    /**
     * Extract @font-face rules.
     *
     * @param string $css
     * @return array
     */
    protected function extractFontFaces(string $css): array
    {
        $fontFaces = [];
        preg_match_all('/@font-face\s*\{([^}]+)\}/is', $css, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $declarations = $this->parseDeclarations($match[1]);
            $fontFaces[] = [
                'font_family' => trim($declarations['font-family'] ?? '', '\'"'),
                'src' => $declarations['src'] ?? null,
                'font_weight' => $declarations['font-weight'] ?? null,
                'font_style' => $declarations['font-style'] ?? null,
            ];
        }

        return $fontFaces;
    }

    /**
     * Extract CSS custom properties (--* variables).
     *
     * @param string $css
     * @return array
     */
    protected function extractCustomProperties(string $css): array
    {
        $vars = [];
        preg_match_all('/(--[a-zA-Z0-9_-]+)\s*:\s*([^;]+);/i', $css, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $vars[$match[1]] = trim($match[2]);
        }

        return $vars;
    }

    /**
     * Extract standard CSS selectors and declarations.
     *
     * @param string $css
     * @return array
     */
    protected function extractStandardRules(string $css): array
    {
        // Strip at-rules (@media, @keyframes, @font-face) to isolate top-level rules
        $stripped = preg_replace('/@(media|-webkit-keyframes|keyframes|font-face|supports)\b[^{]*\{((?:[^{}]+|\{[^{}]*\})*)\}/is', '', $css);

        $rules = [];
        preg_match_all('/([^{]+)\{([^}]+)\}/s', $stripped, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $selectorGroup = trim($match[1]);
            $declarations = $this->parseDeclarations($match[2]);

            // Split comma separated selectors
            $selectors = array_map('trim', explode(',', $selectorGroup));

            foreach ($selectors as $sel) {
                if ($sel === '' || str_starts_with($sel, '@')) {
                    continue;
                }

                $rules[] = [
                    'selector' => $sel,
                    'declarations' => $declarations,
                    'is_layout' => isset($declarations['display']) || isset($declarations['position']) || isset($declarations['flex']) || isset($declarations['grid']),
                    'is_typography' => isset($declarations['font-size']) || isset($declarations['font-family']) || isset($declarations['font-weight']) || isset($declarations['color']),
                ];
            }
        }

        return $rules;
    }

    /**
     * Parse CSS declaration block into associative array.
     *
     * @param string $block
     * @return array
     */
    protected function parseDeclarations(string $block): array
    {
        $declarations = [];
        $items = explode(';', $block);

        foreach ($items as $item) {
            $parts = explode(':', trim($item), 2);
            if (count($parts) === 2) {
                $prop = strtolower(trim($parts[0]));
                $val = trim($parts[1]);
                if ($prop !== '' && $val !== '') {
                    $declarations[$prop] = $val;
                }
            }
        }

        return $declarations;
    }
}
