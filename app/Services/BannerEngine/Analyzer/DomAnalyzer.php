<?php

namespace App\Services\BannerEngine\Analyzer;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class DomAnalyzer
{
    /**
     * Parse HTML string and extract complete structural node inventory.
     *
     * @param string $html
     * @return array
     */
    public function analyzeDom(string $html): array
    {
        if (trim($html) === '') {
            return [
                'elements' => [],
                'text_nodes' => [],
                'media_nodes' => [],
                'interactive_nodes' => [],
                'total_nodes' => 0,
            ];
        }

        $dom = new DOMDocument();
        $prevErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . '<div id="__analyzer_root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $xpath = new DOMXPath($dom);
        $root = $dom->getElementById('__analyzer_root');

        if (!$root) {
            return [
                'elements' => [],
                'text_nodes' => [],
                'media_nodes' => [],
                'interactive_nodes' => [],
                'total_nodes' => 0,
            ];
        }

        $elements = [];
        $textNodes = [];
        $mediaNodes = [];
        $interactiveNodes = [];

        $allElements = $xpath->query('.//*', $root);

        if ($allElements) {
            foreach ($allElements as $element) {
                if ($element instanceof DOMElement) {
                    $nodeData = $this->extractElementData($element, $root);

                    $elements[] = $nodeData;

                    if ($nodeData['is_text_candidate']) {
                        $textNodes[] = $nodeData;
                    }

                    if ($nodeData['is_media_candidate']) {
                        $mediaNodes[] = $nodeData;
                    }

                    if ($nodeData['is_interactive_candidate']) {
                        $interactiveNodes[] = $nodeData;
                    }
                }
            }
        }

        return [
            'elements' => $elements,
            'text_nodes' => $textNodes,
            'media_nodes' => $mediaNodes,
            'interactive_nodes' => $interactiveNodes,
            'total_nodes' => count($elements),
        ];
    }

    /**
     * Extract detailed metadata and fingerprints for a DOM element.
     *
     * @param DOMElement $element
     * @param DOMElement $root
     * @return array
     */
    protected function extractElementData(DOMElement $element, DOMElement $root): array
    {
        $tagName = strtolower($element->tagName);
        $domPath = $this->calculateDomPath($element, $root);
        $selector = $this->calculateCssSelector($element, $root);
        $attributes = $this->extractAttributes($element);
        $directText = $this->getDirectTextContent($element);
        $allText = trim($element->textContent);
        $inlineStyles = $this->parseInlineStyles($attributes['style'] ?? '');

        $textFingerprint = !empty($allText) ? substr(hash('sha256', $allText), 0, 16) : null;
        $elementFingerprint = $this->generateElementFingerprint($element, $domPath, $attributes, $allText);

        $isTextCandidate = $this->isTextElementCandidate($tagName, $allText, $element);
        $isMediaCandidate = in_array($tagName, ['img', 'picture', 'video', 'canvas', 'svg', 'source', 'audio', 'figure'], true);
        $isInteractiveCandidate = $this->isInteractiveCandidate($tagName, $attributes);

        return [
            'tag' => $tagName,
            'dom_path' => $domPath,
            'selector' => $selector,
            'attributes' => $attributes,
            'direct_text' => $directText,
            'text_content' => $allText,
            'text_length' => mb_strlen($allText),
            'inline_styles' => $inlineStyles,
            'classes' => !empty($attributes['class']) ? preg_split('/\s+/', trim($attributes['class'])) : [],
            'id' => $attributes['id'] ?? null,
            'src' => $attributes['src'] ?? ($attributes['data-src'] ?? null),
            'href' => $attributes['href'] ?? null,
            'alt' => $attributes['alt'] ?? null,
            'text_fingerprint' => $textFingerprint,
            'element_fingerprint' => $elementFingerprint,
            'child_element_count' => $element->childElementCount,
            'is_text_candidate' => $isTextCandidate,
            'is_media_candidate' => $isMediaCandidate,
            'is_interactive_candidate' => $isInteractiveCandidate,
        ];
    }

    /**
     * Calculate absolute DOM XPath relative to parser root.
     *
     * @param DOMElement $element
     * @param DOMElement $root
     * @return string
     */
    protected function calculateDomPath(DOMElement $element, DOMElement $root): string
    {
        $path = '';
        $current = $element;

        while ($current !== null && $current !== $root && $current instanceof DOMElement) {
            $tagName = strtolower($current->tagName);
            $index = 1;

            $sibling = $current->previousSibling;
            while ($sibling !== null) {
                if ($sibling instanceof DOMElement && strtolower($sibling->tagName) === $tagName) {
                    $index++;
                }
                $sibling = $sibling->previousSibling;
            }

            $step = "{$tagName}[{$index}]";
            $path = '/' . $step . $path;
            $current = $current->parentNode;
        }

        return $path ?: '/' . strtolower($element->tagName);
    }

    /**
     * Calculate CSS selector for element.
     *
     * @param DOMElement $element
     * @param DOMElement $root
     * @return string
     */
    protected function calculateCssSelector(DOMElement $element, DOMElement $root): string
    {
        $id = $element->getAttribute('id');
        if ($id && !str_starts_with($id, '__')) {
            return '#' . $id;
        }

        $pathSegments = [];
        $current = $element;

        while ($current !== null && $current !== $root && $current instanceof DOMElement) {
            $tagName = strtolower($current->tagName);
            $currId = $current->getAttribute('id');

            if ($currId && !str_starts_with($currId, '__')) {
                $pathSegments[] = '#' . $currId;
                break;
            }

            $classes = array_filter(preg_split('/\s+/', trim($current->getAttribute('class'))));
            if (!empty($classes)) {
                // Use first 2 classes
                $classSelector = '.' . implode('.', array_slice($classes, 0, 2));
                $pathSegments[] = $tagName . $classSelector;
            } else {
                $pathSegments[] = $tagName;
            }

            $current = $current->parentNode;
        }

        return implode(' > ', array_reverse($pathSegments)) ?: strtolower($element->tagName);
    }

    /**
     * Generate multi-point structural fingerprint for element.
     *
     * @param DOMElement $element
     * @param string $domPath
     * @param array $attributes
     * @param string $text
     * @return string
     */
    protected function generateElementFingerprint(DOMElement $element, string $domPath, array $attributes, string $text): string
    {
        $tagName = strtolower($element->tagName);
        $classes = !empty($attributes['class']) ? preg_split('/\s+/', trim($attributes['class'])) : [];
        sort($classes);
        $sortedClassStr = implode('.', $classes);

        $id = $attributes['id'] ?? '';
        $src = $attributes['src'] ?? '';
        $href = $attributes['href'] ?? '';
        $textPrefix = mb_substr(trim($text), 0, 32);

        $payload = implode('|', [
            $tagName,
            $id,
            $sortedClassStr,
            $domPath,
            $src,
            $href,
            $textPrefix,
        ]);

        return substr(hash('sha256', $payload), 0, 20);
    }

    /**
     * Extract attributes of element as key-value map.
     *
     * @param DOMElement $element
     * @return array
     */
    protected function extractAttributes(DOMElement $element): array
    {
        $attrs = [];
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $attrs[$attr->name] = $attr->value;
            }
        }
        return $attrs;
    }

    /**
     * Get direct text content excluding child elements.
     *
     * @param DOMElement $element
     * @return string
     */
    protected function getDirectTextContent(DOMElement $element): string
    {
        $text = '';
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= $child->nodeValue;
            }
        }
        return trim($text);
    }

    /**
     * Parse inline style string into key-value map.
     *
     * @param string $styleStr
     * @return array
     */
    protected function parseInlineStyles(string $styleStr): array
    {
        if (trim($styleStr) === '') {
            return [];
        }

        $styles = [];
        $rules = explode(';', $styleStr);

        foreach ($rules as $rule) {
            $parts = explode(':', $rule, 2);
            if (count($parts) === 2) {
                $prop = strtolower(trim($parts[0]));
                $val = trim($parts[1]);
                if ($prop !== '' && $val !== '') {
                    $styles[$prop] = $val;
                }
            }
        }

        return $styles;
    }

    /**
     * Determine if an element is a viable text content candidate.
     *
     * @param string $tagName
     * @param string $text
     * @param DOMElement $element
     * @return bool
     */
    protected function isTextElementCandidate(string $tagName, string $text, DOMElement $element): bool
    {
        if (trim($text) === '') {
            return false;
        }

        if (in_array($tagName, ['script', 'style', 'noscript', 'svg', 'canvas', 'video', 'audio', 'picture'], true)) {
            return false;
        }

        // Headings, paragraphs, spans, labels, badges, buttons, links
        if (in_array($tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'strong', 'b', 'em', 'small', 'label', 'a', 'button', 'li', 'td', 'th'], true)) {
            return true;
        }

        // Div or section if it has immediate text and low child count
        if (in_array($tagName, ['div', 'section', 'header', 'article', 'aside'], true) && $element->childElementCount <= 2 && !empty($this->getDirectTextContent($element))) {
            return true;
        }

        return false;
    }

    /**
     * Determine if an element is an interactive CTA candidate.
     *
     * @param string $tagName
     * @param array $attributes
     * @return bool
     */
    protected function isInteractiveCandidate(string $tagName, array $attributes): bool
    {
        if (in_array($tagName, ['a', 'button'], true)) {
            return true;
        }

        if ($tagName === 'input' && in_array($attributes['type'] ?? '', ['button', 'submit', 'reset'], true)) {
            return true;
        }

        if (isset($attributes['role']) && in_array(strtolower($attributes['role']), ['button', 'link'], true)) {
            return true;
        }

        return false;
    }
}
