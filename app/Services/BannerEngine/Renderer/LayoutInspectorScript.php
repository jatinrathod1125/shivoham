<?php

namespace App\Services\BannerEngine\Renderer;

class LayoutInspectorScript
{
    /**
     * Get client-side instrumentation script to collect bounding boxes, computed styles, and visibility.
     *
     * @return string
     */
    public static function getInspectorScript(): string
    {
        return <<<'JS'
(function() {
    window.__bannerEngineInspector = {
        collectMetrics: function() {
            var allElements = document.querySelectorAll('*');
            var metrics = [];
            var viewportWidth = window.innerWidth;
            var viewportHeight = window.innerHeight;

            for (var i = 0; i < allElements.length; i++) {
                var el = allElements[i];
                var tag = el.tagName.toLowerCase();

                if (tag === 'html' || tag === 'head' || tag === 'meta' || tag === 'script' || tag === 'style' || tag === 'title' || tag === 'link') {
                    continue;
                }

                var rect = el.getBoundingClientRect();
                var style = window.getComputedStyle(el);

                var isVisible = (
                    rect.width > 0 &&
                    rect.height > 0 &&
                    style.visibility !== 'hidden' &&
                    style.display !== 'none' &&
                    parseFloat(style.opacity || '1') > 0
                );

                var isOverflowingX = (rect.right > viewportWidth || rect.left < 0);
                var isOverflowingY = (rect.bottom > viewportHeight || rect.top < 0);

                var zIndexVal = parseInt(style.zIndex, 10);
                var zIndex = isNaN(zIndexVal) ? 0 : zIndexVal;

                var fontSizeVal = parseFloat(style.fontSize || '16');

                // Visual prominence score (area * font-size weight * visibility)
                var area = rect.width * rect.height;
                var prominenceScore = isVisible ? (area * (fontSizeVal / 16)) : 0;

                metrics.push({
                    tag: tag,
                    dom_path: window.__bannerEngineInspector.computeDomPath(el),
                    bounding_box: {
                        x: Math.round(rect.x),
                        y: Math.round(rect.y),
                        top: Math.round(rect.top),
                        right: Math.round(rect.right),
                        bottom: Math.round(rect.bottom),
                        left: Math.round(rect.left),
                        width: Math.round(rect.width),
                        height: Math.round(rect.height)
                    },
                    computed_styles: {
                        font_size: style.fontSize,
                        font_weight: style.fontWeight,
                        font_family: style.fontFamily,
                        color: style.color,
                        background_color: style.backgroundColor,
                        text_align: style.textAlign,
                        display: style.display,
                        position: style.position,
                        z_index: zIndex,
                        opacity: style.opacity,
                        overflow: style.overflow
                    },
                    is_visible: isVisible,
                    is_overflowing_x: isOverflowingX,
                    is_overflowing_y: isOverflowingY,
                    prominence_score: Math.round(prominenceScore),
                    text_preview: el.textContent ? el.textContent.trim().substring(0, 60) : ''
                });
            }

            return {
                viewport: {
                    width: viewportWidth,
                    height: viewportHeight,
                    device_pixel_ratio: window.devicePixelRatio || 1
                },
                elements_count: metrics.length,
                elements: metrics
            };
        },

        computeDomPath: function(el) {
            var path = '';
            var current = el;
            while (current && current.nodeType === Node.ELEMENT_NODE && current.tagName.toLowerCase() !== 'body' && current.tagName.toLowerCase() !== 'html') {
                var tag = current.tagName.toLowerCase();
                var index = 1;
                var sib = current.previousElementSibling;
                while (sib) {
                    if (sib.tagName.toLowerCase() === tag) {
                        index++;
                    }
                    sib = sib.previousElementSibling;
                }
                path = '/' + tag + '[' + index + ']' + path;
                current = current.parentElement;
            }
            return path || '/' + el.tagName.toLowerCase();
        }
    };
})();
JS;
    }
}
