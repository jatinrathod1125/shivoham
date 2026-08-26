<?php

namespace App\Services\BannerEngine\Sanitizer;

use App\Services\BannerEngine\Contracts\SanitizerInterface;
use DOMDocument;
use DOMElement;
use DOMXPath;

class HtmlSanitizer implements SanitizerInterface
{
    /**
     * Dangerous event handler attributes.
     */
    protected array $dangerousAttributes = [
        'onload', 'onerror', 'onclick', 'ondblclick', 'onmousedown', 'onmouseup',
        'onmouseover', 'onmousemove', 'onmouseout', 'onmouseenter', 'onmouseleave',
        'onkeydown', 'onkeypress', 'onkeyup', 'onfocus', 'onblur', 'onsubmit',
        'onreset', 'onselect', 'onchange', 'formaction', 'onanimationstart',
        'onanimationend', 'ontoggle', 'onpointerdown', 'onpointerup',
    ];

    /**
     * Dangerous tags that must never be directly injected outside controlled sandboxes.
     */
    protected array $dangerousTags = [
        'meta', 'applet', 'embed', 'object', 'base', 'frame', 'frameset',
    ];

    /**
     * Sanitize raw HTML markup while preserving design layout, SVG, canvas, and video.
     *
     * @param string $html
     * @return string
     */
    public function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // Clean javascript: and vbscript: URIs
        $html = preg_replace('/(?i)(javascript|vbscript|data\s*:\s*text\/html)\s*:/', 'blocked-uri:', $html);

        // Load DOM for structural cleansing
        $dom = new DOMDocument();
        $previousLibxmlUseErrors = libxml_use_internal_errors(true);

        // Encode as UTF-8 HTML fragment
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . '<div id="__banner_sandbox_wrapper">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlUseErrors);

        $xpath = new DOMXPath($dom);

        // 1. Remove dangerous tags
        foreach ($this->dangerousTags as $tag) {
            $elements = $xpath->query("//{$tag}");
            if ($elements) {
                foreach ($elements as $el) {
                    $el->parentNode?->removeChild($el);
                }
            }
        }

        // 2. Remove dangerous attributes across all elements
        $allElements = $xpath->query('//*');
        if ($allElements) {
            foreach ($allElements as $element) {
                if ($element instanceof DOMElement) {
                    foreach ($this->dangerousAttributes as $attr) {
                        if ($element->hasAttribute($attr)) {
                            $element->removeAttribute($attr);
                        }
                    }

                    // Check href/src for blocked or javascript URIs
                    foreach (['href', 'src', 'action', 'data', 'poster'] as $urlAttr) {
                        if ($element->hasAttribute($urlAttr)) {
                            $val = trim($element->getAttribute($urlAttr));
                            if (preg_match('/^(javascript|vbscript):/i', $val)) {
                                $element->setAttribute($urlAttr, '#blocked');
                            }
                        }
                    }
                }
            }
        }

        // Extract inner HTML of wrapper
        $wrapper = $dom->getElementById('__banner_sandbox_wrapper');
        if (!$wrapper) {
            return $html;
        }

        $cleanHtml = '';
        foreach ($wrapper->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }

        return trim($cleanHtml);
    }

    /**
     * Sanitize raw CSS stylesheet content.
     *
     * @param string $css
     * @return string
     */
    public function sanitizeCss(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }

        // Strip behavior / expression CSS hacks
        $css = preg_replace('/(?i)behavior\s*:\s*url/i', 'behavior: none', $css);
        $css = preg_replace('/(?i)-moz-binding\s*:/i', '-moz-binding-blocked:', $css);
        $css = preg_replace('/(?i)expression\s*\(/i', 'blocked-expression(', $css);
        $css = preg_replace('/(?i)(javascript|vbscript)\s*:/i', 'blocked:', $css);

        return $css;
    }

    /**
     * Sanitize SVG markup.
     *
     * @param string $svg
     * @return string
     */
    public function sanitizeSvg(string $svg): string
    {
        if (trim($svg) === '') {
            return '';
        }

        // Strip DOCTYPE entity expansions (XXE prevention)
        $svg = preg_replace('/<!DOCTYPE[^>]*>/i', '', $svg);
        $svg = preg_replace('/<!ENTITY[^>]*>/i', '', $svg);

        // Strip script tags inside SVGs
        $svg = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $svg);

        // Strip dangerous attributes
        foreach ($this->dangerousAttributes as $attr) {
            $svg = preg_replace('/' . preg_quote($attr, '/') . '\s*=\s*"[^"]*"/i', '', $svg);
            $svg = preg_replace('/' . preg_quote($attr, '/') . '\s*=\s*\'[^\']*\'/i', '', $svg);
        }

        return $svg;
    }
}
