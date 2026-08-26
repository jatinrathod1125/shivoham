<?php

namespace App\Services\BannerEngine\Analyzer;

use App\Models\BannerAnalysis;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\BannerEngineManager;
use App\Services\BannerEngine\Contracts\AnalyzerInterface;
use Illuminate\Support\Str;

class StructuralAnalysisEngine implements AnalyzerInterface
{
    protected DomAnalyzer $domAnalyzer;
    protected CssAnalyzer $cssAnalyzer;

    public function __construct(?DomAnalyzer $domAnalyzer = null, ?CssAnalyzer $cssAnalyzer = null)
    {
        $this->domAnalyzer = $domAnalyzer ?? new DomAnalyzer();
        $this->cssAnalyzer = $cssAnalyzer ?? new CssAnalyzer();
    }

    /**
     * Analyze a template structure and extract semantic elements.
     *
     * @param BannerTemplate $template
     * @param array $options
     * @return BannerAnalysis
     */
    public function analyze(BannerTemplate $template, array $options = []): BannerAnalysis
    {
        $domResult = $this->domAnalyzer->analyzeDom($template->raw_html);
        $cssResult = $this->cssAnalyzer->analyzeCss($template->raw_css ?? '');

        $schemaElements = [];
        $editableCount = 0;
        $lockedCount = 0;
        $totalConfidence = 0.0;

        $fieldSortOrder = 0;

        foreach ($domResult['elements'] as $element) {
            $analysis = $this->classifyElementSemantics($element, $cssResult);

            if ($analysis['should_include_in_schema']) {
                $fieldKey = 'fld_' . substr(hash('sha256', $element['dom_path'] . '|' . $element['element_fingerprint']), 0, 8);

                $schemaEntry = [
                    'field_key' => $fieldKey,
                    'semantic_role' => $analysis['role'],
                    'label' => $analysis['label'],
                    'field_type' => $analysis['field_type'],
                    'default_value' => $analysis['default_value'],
                    'dom_path' => $element['dom_path'],
                    'selector' => $element['selector'],
                    'text_fingerprint' => $element['text_fingerprint'],
                    'element_fingerprint' => $element['element_fingerprint'],
                    'confidence_score' => $analysis['confidence'],
                    'confidence_status' => BannerEngineManager::classifyConfidence($analysis['confidence']),
                    'detection_reason' => $analysis['reason'],
                    'is_editable' => $analysis['is_editable'],
                    'is_locked' => !$analysis['is_editable'],
                    'sort_order' => $fieldSortOrder++,
                ];

                $schemaElements[] = $schemaEntry;

                if ($analysis['is_editable']) {
                    $editableCount++;
                } else {
                    $lockedCount++;
                }

                $totalConfidence += $analysis['confidence'];
            }
        }

        $detectedCount = count($schemaElements);
        $overallConfidence = $detectedCount > 0 ? round($totalConfidence / $detectedCount, 4) : 1.0;

        // Persist schema in template
        $template->update([
            'dynamic_schema' => [
                'elements' => $schemaElements,
                'css_summary' => [
                    'rule_count' => $cssResult['total_rules'],
                    'media_query_count' => count($cssResult['media_queries']),
                    'keyframes_count' => count($cssResult['keyframes']),
                    'font_face_count' => count($cssResult['font_faces']),
                ],
                'analyzed_at' => now()->toIso8601String(),
            ],
        ]);

        // Sync BannerField models
        $this->syncBannerFields($template, $schemaElements);

        // Record BannerAnalysis
        return BannerAnalysis::create([
            'template_id' => $template->id,
            'analysis_engine' => BannerAnalysis::ENGINE_DOM_HEURISTIC,
            'status' => BannerAnalysis::STATUS_COMPLETED,
            'overall_confidence' => $overallConfidence,
            'elements_detected_count' => $detectedCount,
            'editable_elements_count' => $editableCount,
            'locked_elements_count' => $lockedCount,
            'detected_schema' => $schemaElements,
        ]);
    }

    /**
     * Classify an individual element into semantic role, confidence score, and editable status.
     *
     * @param array $element
     * @param array $cssResult
     * @return array
     */
    protected function classifyElementSemantics(array $element, array $cssResult): array
    {
        $tag = $element['tag'];
        $text = trim($element['text_content']);
        $directText = trim($element['direct_text']);
        $attrs = $element['attributes'];

        // Default classification
        $role = 'unknown';
        $label = 'Element (' . strtoupper($tag) . ')';
        $fieldType = 'text';
        $defaultValue = $text;
        $isEditable = false;
        $confidence = 0.50;
        $reason = "General DOM element <{$tag}>";
        $shouldInclude = false;

        // 1. Price & Discount Detection via Currency/Number heuristics
        if ($element['is_text_candidate'] && !($tag === 'p' && mb_strlen($text) > 30) && preg_match('/(?:[\$€£₹¥]|USD|INR|EUR)\s*[\d,]+(?:\.\d{2})?|\b\d+\s*%/i', $text)) {
            if (preg_match('/\b\d+\s*%/i', $text) && (mb_strlen($text) <= 30 || preg_match('/\b(?:off|discount|save|sale)\b/i', $text))) {
                $role = 'discount';
                $label = 'Discount Offer Badge';
                $fieldType = 'text';
                $isEditable = true;
                $confidence = 0.92;
                $reason = "Text contains percentage discount pattern ({$text})";
                $shouldInclude = true;
            } elseif (preg_match('/(?:[\$€£₹¥]|USD|INR|EUR)\s*[\d,]+(?:\.\d{2})?/i', $text)) {
                $role = 'price';
                $label = 'Product Price';
                $fieldType = 'price';
                $isEditable = true;
                $confidence = 0.94;
                $reason = "Text contains currency formatted price ({$text})";
                $shouldInclude = true;
            }
        }

        // 2. Buttons / Interactive CTAs
        elseif ($tag === 'button' || $element['is_interactive_candidate']) {
            $role = 'cta';
            $label = 'Call to Action Button';
            $fieldType = 'cta';
            $defaultValue = $text ?: ($attrs['aria-label'] ?? 'Button');
            $isEditable = true;
            $confidence = 0.94;
            $reason = "Interactive <{$tag}> button or action trigger";
            $shouldInclude = true;
        }

        // 3. Images and Visual Media
        elseif ($tag === 'img') {
            $role = 'product_image';
            $label = !empty($attrs['alt']) ? 'Image (' . $attrs['alt'] . ')' : 'Featured Product Image';
            $fieldType = 'image';
            $defaultValue = $element['src'] ?? '';
            $isEditable = true;
            $confidence = !empty($attrs['alt']) ? 0.92 : 0.85;
            $reason = "Image element <img src='{$element['src']}'>";
            $shouldInclude = true;
        } elseif ($tag === 'video') {
            $role = 'video';
            $label = 'Promotional Video';
            $fieldType = 'video';
            $defaultValue = $element['src'] ?? '';
            $isEditable = true;
            $confidence = 0.90;
            $reason = "HTML5 <video> media container";
            $shouldInclude = true;
        }

        // 4. Headings (h1, h2, h3, h4, h5, h6)
        elseif (in_array($tag, ['h1', 'h2'], true) && !empty($text)) {
            $role = 'headline';
            $label = $tag === 'h1' ? 'Main Headline' : 'Sub-Headline';
            $fieldType = 'text';
            $isEditable = true;
            $confidence = 0.96;
            $reason = "Primary {$tag} header tag with prominent title text";
            $shouldInclude = true;
        } elseif (in_array($tag, ['h3', 'h4', 'h5', 'h6'], true) && !empty($text)) {
            $role = 'subtitle';
            $label = 'Subtitle';
            $fieldType = 'text';
            $isEditable = true;
            $confidence = 0.90;
            $reason = "Secondary header element <{$tag}>";
            $shouldInclude = true;
        }

        // 5. Promotional Eyebrows / Badges (Short capitalized text)
        elseif ($element['is_text_candidate'] && in_array($tag, ['span', 'small', 'strong', 'b', 'label', 'p'], true) && mb_strlen($text) < 30 && preg_match('/^[A-Z0-9\s!%-]+$/', $text) && !empty($text)) {
            $role = 'badge';
            $label = 'Promotional Badge / Tag';
            $fieldType = 'text';
            $isEditable = true;
            $confidence = 0.88;
            $reason = "Short uppercase promotional badge text ({$text})";
            $shouldInclude = true;
        }

        // 6. Paragraph / Descriptive copy
        elseif ($tag === 'p' && !empty($text)) {
            $role = 'description';
            $label = 'Description Copy';
            $fieldType = 'text';
            $isEditable = true;
            $confidence = 0.89;
            $reason = "Paragraph descriptive text block";
            $shouldInclude = true;
        }

        // 7. General text containers with direct text
        elseif ($element['is_text_candidate'] && in_array($tag, ['span', 'strong', 'em', 'li', 'div'], true) && !empty($directText)) {
            $role = 'subtitle';
            $label = 'Content Text';
            $fieldType = 'text';
            $defaultValue = $directText;
            $isEditable = true;
            $confidence = 0.78;
            $reason = "Inline text element <{$tag}>";
            $shouldInclude = true;
        }

        // 8. 3D Canvas / Background Layers (Locked Design)
        elseif (in_array($tag, ['canvas', 'svg'], true)) {
            $role = $tag === 'canvas' ? 'animation' : 'decorative';
            $label = $tag === 'canvas' ? '3D / WebGL Canvas Layer' : 'SVG Vector Decoration';
            $fieldType = 'unknown';
            $isEditable = false;
            $confidence = 0.95;
            $reason = "Design layout asset <{$tag}> (locked)";
            $shouldInclude = true;
        }

        return [
            'should_include_in_schema' => $shouldInclude,
            'role' => $role,
            'label' => $label,
            'field_type' => $fieldType,
            'default_value' => $defaultValue,
            'confidence' => $confidence,
            'reason' => $reason,
            'is_editable' => $isEditable,
        ];
    }

    /**
     * Synchronize BannerField records in database from schema.
     *
     * @param BannerTemplate $template
     * @param array $schemaElements
     * @return void
     */
    protected function syncBannerFields(BannerTemplate $template, array $schemaElements): void
    {
        $existingFieldKeys = [];

        foreach ($schemaElements as $item) {
            $field = BannerField::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'field_key' => $item['field_key'],
                ],
                [
                    'semantic_role' => $item['semantic_role'],
                    'label' => $item['label'],
                    'field_type' => $item['field_type'],
                    'default_value' => $item['default_value'],
                    'dom_path' => $item['dom_path'],
                    'selector' => $item['selector'],
                    'text_fingerprint' => $item['text_fingerprint'],
                    'element_fingerprint' => $item['element_fingerprint'],
                    'confidence_score' => $item['confidence_score'],
                    'confidence_status' => $item['confidence_status'],
                    'detection_reason' => $item['detection_reason'],
                    'is_editable' => $item['is_editable'],
                    'is_locked' => $item['is_locked'],
                    'sort_order' => $item['sort_order'],
                ]
            );

            $existingFieldKeys[] = $field->field_key;
        }

        // Clean up orphaned fields if re-analyzing
        $template->fields()->whereNotIn('field_key', $existingFieldKeys)->delete();
    }
}
