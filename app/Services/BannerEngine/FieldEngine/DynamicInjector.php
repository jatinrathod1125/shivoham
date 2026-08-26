<?php

namespace App\Services\BannerEngine\FieldEngine;

use App\Models\BannerField;
use App\Models\BannerTemplate;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class DynamicInjector
{
    /**
     * Inject dynamic field values into the original HTML template preserving all layout and design intact.
     *
     * @param string $html
     * @param BannerTemplate $template
     * @param array $fieldValues
     * @return string
     */
    public function inject(string $html, BannerTemplate $template, array $fieldValues): string
    {
        if (trim($html) === '' || empty($fieldValues)) {
            return $html;
        }

        $dom = new DOMDocument();
        $prevErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . '<div id="__injector_root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $xpath = new DOMXPath($dom);
        $root = $dom->getElementById('__injector_root');

        if (!$root) {
            return $html;
        }

        $fields = $template->fields()->get()->keyBy('field_key');

        foreach ($fieldValues as $fieldKey => $value) {
            $field = $fields->get($fieldKey);

            // If not found by field_key, try finding by semantic_role
            if (!$field) {
                $field = $fields->firstWhere('semantic_role', $fieldKey);
            }

            if (!$field || $value === null) {
                continue;
            }

            // Locate element in DOM using multi-point fallback
            $element = $this->locateElement($xpath, $root, $field);

            if ($element) {
                $hardener = new \App\Services\BannerEngine\Security\SecurityHardener();
                $sanitizedValue = $hardener->sanitizeFieldValue($value);
                $this->applyValueToElement($dom, $element, $field, $sanitizedValue);
            }
        }

        $cleanHtml = '';
        foreach ($root->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }

        return trim($cleanHtml);
    }

    /**
     * Locate element in DOM tree using multi-point fallback locators.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $root
     * @param BannerField $field
     * @return DOMElement|null
     */
    protected function locateElement(DOMXPath $xpath, DOMElement $root, BannerField $field): ?DOMElement
    {
        // 1. Primary: Exact DOM XPath
        if (!empty($field->dom_path)) {
            $query = '.' . $field->dom_path;
            $nodes = $xpath->query($query, $root);
            if ($nodes && $nodes->length > 0 && $nodes->item(0) instanceof DOMElement) {
                return $nodes->item(0);
            }
        }

        // 2. Secondary: CSS Selector lookup via XPath translation
        if (!empty($field->selector)) {
            $nodes = $this->queryBySelector($xpath, $root, $field->selector);
            if ($nodes && $nodes->length > 0 && $nodes->item(0) instanceof DOMElement) {
                return $nodes->item(0);
            }
        }

        // 3. Tertiary: Structural Fingerprint matching
        if (!empty($field->element_fingerprint)) {
            $allElements = $xpath->query('.//*', $root);
            if ($allElements) {
                foreach ($allElements as $el) {
                    if ($el instanceof DOMElement) {
                        $fingerprint = $this->computeElementFingerprint($el, $root);
                        if ($fingerprint === $field->element_fingerprint) {
                            return $el;
                        }
                    }
                }
            }
        }

        // 4. Quaternary: Text Hash matching
        if (!empty($field->text_fingerprint)) {
            $allElements = $xpath->query('.//*', $root);
            if ($allElements) {
                foreach ($allElements as $el) {
                    if ($el instanceof DOMElement && !empty(trim($el->textContent))) {
                        $hash = substr(hash('sha256', trim($el->textContent)), 0, 16);
                        if ($hash === $field->text_fingerprint) {
                            return $el;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Apply dynamic value into DOM element according to field type.
     *
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @param BannerField $field
     * @param mixed $value
     * @return void
     */
    protected function applyValueToElement(DOMDocument $dom, DOMElement $element, BannerField $field, mixed $value): void
    {
        $type = $field->field_type;
        $tag = strtolower($element->tagName);

        // A. Image field
        if ($type === FieldExtractor::TYPE_IMAGE || $tag === 'img') {
            $src = is_array($value) ? ($value['url'] ?? ($value['src'] ?? '')) : (string)$value;
            $alt = is_array($value) ? ($value['alt'] ?? null) : null;

            if (!empty($src)) {
                $element->setAttribute('src', $src);
            }
            if ($alt !== null) {
                $element->setAttribute('alt', $alt);
            }
            return;
        }

        // B. Video field
        if ($type === FieldExtractor::TYPE_VIDEO || $tag === 'video') {
            $src = is_array($value) ? ($value['url'] ?? ($value['src'] ?? '')) : (string)$value;
            $poster = is_array($value) ? ($value['poster'] ?? null) : null;

            if (!empty($src)) {
                if ($element->hasAttribute('src')) {
                    $element->setAttribute('src', $src);
                } else {
                    // Update <source> child if present
                    foreach ($element->getElementsByTagName('source') as $source) {
                        $source->setAttribute('src', $src);
                    }
                }
            }
            if (!empty($poster)) {
                $element->setAttribute('poster', $poster);
            }
            return;
        }

        // C. Call to Action (CTA) field
        if ($type === FieldExtractor::TYPE_CTA || in_array($tag, ['a', 'button'], true)) {
            $text = is_array($value) ? ($value['text'] ?? ($value['label'] ?? '')) : (string)$value;
            $url = is_array($value) ? ($value['url'] ?? ($value['href'] ?? ($value['link'] ?? null))) : null;

            if (!empty($text)) {
                $this->replaceTextPreservingChildren($dom, $element, $text);
            }

            if (!empty($url) && $tag === 'a') {
                $element->setAttribute('href', $url);
            }
            return;
        }

        // D. Date / Countdown Timer
        if ($type === FieldExtractor::TYPE_DATE || $type === FieldExtractor::TYPE_TIMER) {
            $dateStr = is_array($value) ? ($value['date'] ?? ($value['value'] ?? '')) : (string)$value;
            $displayLabel = is_array($value) ? ($value['label'] ?? $dateStr) : $dateStr;

            $element->setAttribute('data-target-date', $dateStr);
            if ($tag === 'time') {
                $element->setAttribute('datetime', $dateStr);
            }
            $this->replaceTextPreservingChildren($dom, $element, $displayLabel);
            return;
        }

        // E. Rich text HTML
        if ($type === FieldExtractor::TYPE_RICH_TEXT) {
            $this->replaceWithRichText($dom, $element, (string)$value);
            return;
        }

        // F. Default Plain Text / Price / Discount / Headline / Subtitle / Badge
        $textValue = is_array($value) ? ($value['value'] ?? ($value['text'] ?? json_encode($value))) : (string)$value;
        $this->replaceTextPreservingChildren($dom, $element, $textValue);
    }

    /**
     * Replace text content of element safely.
     *
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @param string $text
     * @return void
     */
    protected function replaceTextPreservingChildren(DOMDocument $dom, DOMElement $element, string $text): void
    {
        // If element has no child elements, directly update nodeValue
        if ($element->childElementCount === 0) {
            $element->nodeValue = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            return;
        }

        // Find primary text node child
        $found = false;
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE && trim($child->nodeValue) !== '') {
                $child->nodeValue = $text;
                $found = true;
                break;
            }
        }

        if (!$found) {
            // Append or replace
            while ($element->hasChildNodes()) {
                $element->removeChild($element->firstChild);
            }
            $element->appendChild($dom->createTextNode($text));
        }
    }

    /**
     * Replace element contents with safe rich text snippet.
     *
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @param string $richText
     * @return void
     */
    protected function replaceWithRichText(DOMDocument $dom, DOMElement $element, string $richText): void
    {
        while ($element->hasChildNodes()) {
            $element->removeChild($element->firstChild);
        }

        $fragment = $dom->createDocumentFragment();
        $prevErrors = libxml_use_internal_errors(true);
        $fragment->appendXML($richText);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $element->appendChild($fragment);
    }

    /**
     * Query element by simple ID or tag.class selector.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $root
     * @param string $selector
     * @return \DOMNodeList|false
     */
    protected function queryBySelector(DOMXPath $xpath, DOMElement $root, string $selector)
    {
        $selector = trim($selector);

        // ID selector: #main-heading
        if (preg_match('/^#([a-zA-Z0-9_-]+)$/', $selector, $m)) {
            return $xpath->query('.//*[@id="' . $m[1] . '"]', $root);
        }

        // Tag.class selector: h1.title.hero
        if (preg_match('/^([a-zA-Z0-9_-]+)?\.([a-zA-Z0-9_.-]+)$/', $selector, $m)) {
            $tag = $m[1] ?: '*';
            $firstClass = explode('.', $m[2])[0];
            return $xpath->query('.//' . $tag . '[contains(concat(" ", normalize-space(@class), " "), " ' . $firstClass . ' ")]', $root);
        }

        return false;
    }

    /**
     * Compute structural fingerprint for an element.
     *
     * @param DOMElement $element
     * @param DOMElement $root
     * @return string
     */
    protected function computeElementFingerprint(DOMElement $element, DOMElement $root): string
    {
        $tagName = strtolower($element->tagName);
        $classes = !empty($element->getAttribute('class')) ? preg_split('/\s+/', trim($element->getAttribute('class'))) : [];
        sort($classes);
        $sortedClassStr = implode('.', $classes);

        $id = $element->getAttribute('id');
        $src = $element->getAttribute('src');
        $href = $element->getAttribute('href');
        $textPrefix = mb_substr(trim($element->textContent), 0, 32);

        $domPath = $this->calculateDomPath($element, $root);

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
}
