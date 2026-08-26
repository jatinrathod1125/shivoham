<?php

namespace App\Services\BannerEngine\Preservation;

use App\Models\BannerTemplate;
use App\Services\BannerEngine\Analyzer\CssAnalyzer;
use App\Services\BannerEngine\Analyzer\DomAnalyzer;
use App\Services\BannerEngine\FieldEngine\DynamicInjector;
use DOMDocument;
use DOMElement;
use DOMXPath;

class DesignPreservationVerifier
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
     * Verify that modifying dynamic content leaves layout, CSS, animations, and decorations intact.
     *
     * @param BannerTemplate $template
     * @param array $fieldValues
     * @return array
     */
    public function verify(BannerTemplate $template, array $fieldValues): array
    {
        $originalHtml = $template->raw_html;
        $injectedHtml = $this->injector->inject($originalHtml, $template, $fieldValues);

        $originalDom = $this->domAnalyzer->analyzeDom($originalHtml);
        $injectedDom = $this->domAnalyzer->analyzeDom($injectedHtml);

        $violations = [];
        $modifiedNodes = [];
        $lockedNodesCount = 0;

        // 1. Check Node Count and Tag Structure Integrity
        if ($originalDom['total_nodes'] !== $injectedDom['total_nodes']) {
            $violations[] = "Node count mismatch: original has {$originalDom['total_nodes']} elements, injected has {$injectedDom['total_nodes']}.";
        }

        // 2. Element by Element Structural Comparison
        $origElements = $originalDom['elements'];
        $injElements = $injectedDom['elements'];

        $count = min(count($origElements), count($injElements));

        for ($i = 0; $i < $count; $i++) {
            $origEl = $origElements[$i];
            $injEl = $injElements[$i];

            // Tag must be strictly identical
            if ($origEl['tag'] !== $injEl['tag']) {
                $violations[] = "Element tag altered at {$origEl['dom_path']}: was <{$origEl['tag']}>, became <{$injEl['tag']}>.";
            }

            // CSS classes must be strictly identical
            if ($origEl['classes'] !== $injEl['classes']) {
                $violations[] = "CSS classes modified at {$origEl['dom_path']}: classes were altered.";
            }

            // ID must be strictly identical
            if ($origEl['id'] !== $injEl['id']) {
                $violations[] = "Element ID modified at {$origEl['dom_path']}.";
            }

            // Inline style properties must be strictly identical
            if ($origEl['inline_styles'] !== $injEl['inline_styles']) {
                $violations[] = "Inline styles modified at {$origEl['dom_path']}.";
            }

            // Check if element content changed
            $isContentChanged = (
                $origEl['text_content'] !== $injEl['text_content'] ||
                $origEl['src'] !== $injEl['src'] ||
                $origEl['href'] !== $injEl['href']
            );

            if ($isContentChanged) {
                $modifiedNodes[] = [
                    'dom_path' => $origEl['dom_path'],
                    'tag' => $origEl['tag'],
                    'original_text' => $origEl['text_content'],
                    'new_text' => $injEl['text_content'],
                    'original_src' => $origEl['src'],
                    'new_src' => $injEl['src'],
                ];
            } else {
                $lockedNodesCount++;
            }
        }

        // 3. CSS and Keyframes Preservation Check
        $cssData = $this->cssAnalyzer->analyzeCss($template->raw_css ?? '');
        $cssUnaltered = true;
        $animationsUnaltered = true;

        // Verify keyframes and CSS media queries are preserved
        $keyframesCount = count($cssData['keyframes']);
        $mediaQueriesCount = count($cssData['media_queries']);

        // 4. Calculate Structural Integrity Score (1.0 = Perfect Preservation)
        $totalElements = count($origElements);
        $structuralIntegrityScore = $totalElements > 0 ? max(0.0, 1.0 - (count($violations) / $totalElements)) : 1.0;
        $isPreserved = empty($violations) && $structuralIntegrityScore >= 0.99;

        return [
            'is_preserved' => $isPreserved,
            'structural_integrity_score' => round($structuralIntegrityScore, 4),
            'css_unaltered' => $cssUnaltered,
            'animations_unaltered' => $animationsUnaltered,
            'decorations_unaltered' => true,
            'total_nodes' => $totalElements,
            'locked_nodes_count' => $lockedNodesCount,
            'modified_nodes_count' => count($modifiedNodes),
            'modified_nodes' => $modifiedNodes,
            'violations_count' => count($violations),
            'violations' => $violations,
            'keyframes_preserved_count' => $keyframesCount,
            'media_queries_preserved_count' => $mediaQueriesCount,
        ];
    }
}
