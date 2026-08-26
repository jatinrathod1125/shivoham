<?php

namespace App\Services\BannerEngine\Analyzer;

use App\Models\BannerAnalysis;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\BannerEngineManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSemanticClassifier
{
    protected DomAnalyzer $domAnalyzer;
    protected CssAnalyzer $cssAnalyzer;

    public function __construct(?DomAnalyzer $domAnalyzer = null, ?CssAnalyzer $cssAnalyzer = null)
    {
        $this->domAnalyzer = $domAnalyzer ?? new DomAnalyzer();
        $this->cssAnalyzer = $cssAnalyzer ?? new CssAnalyzer();
    }

    /**
     * Run multimodal semantic analysis on a template.
     *
     * @param BannerTemplate $template
     * @param array $options
     * @return BannerAnalysis
     */
    public function analyze(BannerTemplate $template, array $options = []): BannerAnalysis
    {
        // 1. Collect DOM & CSS structural data
        $domData = $this->domAnalyzer->analyzeDom($template->raw_html);
        $cssData = $this->cssAnalyzer->analyzeCss($template->raw_css ?? '');
        $viewportData = $template->viewports ?? [];
        $assetManifest = $template->asset_manifest ?? [];

        // 2. Synthesize multimodal prompt
        $promptContext = $this->buildAnalysisPromptContext($template, $domData, $cssData, $viewportData, $assetManifest);

        // 3. Call AI endpoint or execute deterministic fallback
        $aiResult = null;
        $rawPrompt = json_encode($promptContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $rawResponse = null;

        if ($this->isAiConfigured()) {
            try {
                $aiResult = $this->callAiApi($promptContext, $template);
                $rawResponse = json_encode($aiResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } catch (\Throwable $e) {
                Log::warning('[BannerEngine AI] AI API call failed, falling back to deterministic heuristics: ' . $e->getMessage());
            }
        }

        // 4. If AI result is unavailable, generate comprehensive fallback schema
        if (!$aiResult || empty($aiResult['elements'])) {
            $aiResult = $this->generateHeuristicAiSchema($domData, $cssData, $viewportData);
            $rawResponse = json_encode($aiResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // 5. Synthesize final dynamic schema
        $schemaElements = [];
        $editableCount = 0;
        $lockedCount = 0;
        $totalConfidence = 0.0;
        $sortOrder = 0;

        foreach ($aiResult['elements'] as $elementPrediction) {
            $domPath = $elementPrediction['dom_path'] ?? ($elementPrediction['element_reference'] ?? '');
            $fingerprint = $elementPrediction['element_fingerprint'] ?? '';
            $fieldKey = $elementPrediction['field_key'] ?? ('fld_' . substr(hash('sha256', $domPath . '|' . $fingerprint), 0, 8));

            $confidence = floatval($elementPrediction['confidence'] ?? 0.85);
            $isEditable = (bool)($elementPrediction['editable'] ?? true);
            $role = $elementPrediction['role'] ?? 'unknown';

            $schemaEntry = [
                'field_key' => $fieldKey,
                'semantic_role' => $role,
                'label' => $elementPrediction['label'] ?? ucfirst(str_replace('_', ' ', $role)),
                'field_type' => $elementPrediction['field_type'] ?? 'text',
                'default_value' => $elementPrediction['default_value'] ?? ($elementPrediction['value'] ?? ''),
                'dom_path' => $domPath,
                'selector' => $elementPrediction['selector'] ?? '',
                'text_fingerprint' => $elementPrediction['text_fingerprint'] ?? null,
                'element_fingerprint' => $fingerprint,
                'confidence_score' => $confidence,
                'confidence_status' => BannerEngineManager::classifyConfidence($confidence),
                'detection_reason' => $elementPrediction['reason'] ?? 'AI semantic classification',
                'is_editable' => $isEditable,
                'is_locked' => !$isEditable,
                'sort_order' => $sortOrder++,
            ];

            $schemaElements[] = $schemaEntry;

            if ($isEditable) {
                $editableCount++;
            } else {
                $lockedCount++;
            }

            $totalConfidence += $confidence;
        }

        $detectedCount = count($schemaElements);
        $overallConfidence = $detectedCount > 0 ? round($totalConfidence / $detectedCount, 4) : 1.0;

        // 6. Persist dynamic schema to BannerTemplate
        $template->update([
            'dynamic_schema' => [
                'elements' => $schemaElements,
                'overall_confidence' => $overallConfidence,
                'ai_analyzed_at' => now()->toIso8601String(),
            ],
        ]);

        // 7. Sync BannerField records
        $this->syncBannerFields($template, $schemaElements);

        // 8. Record BannerAnalysis
        return BannerAnalysis::create([
            'template_id' => $template->id,
            'analysis_engine' => $this->isAiConfigured() ? BannerAnalysis::ENGINE_MULTIMODAL_AI : BannerAnalysis::ENGINE_DOM_HEURISTIC,
            'status' => BannerAnalysis::STATUS_COMPLETED,
            'overall_confidence' => $overallConfidence,
            'elements_detected_count' => $detectedCount,
            'editable_elements_count' => $editableCount,
            'locked_elements_count' => $lockedCount,
            'raw_prompt' => $rawPrompt,
            'raw_response' => $rawResponse,
            'detected_schema' => $schemaElements,
        ]);
    }

    /**
     * Build structured prompt context for AI model.
     *
     * @param BannerTemplate $template
     * @param array $domData
     * @param array $cssData
     * @param array $viewportData
     * @param array $assetManifest
     * @return array
     */
    public function buildAnalysisPromptContext(BannerTemplate $template, array $domData, array $cssData, array $viewportData, array $assetManifest): array
    {
        $candidateElements = [];

        foreach ($domData['elements'] as $el) {
            if ($el['is_text_candidate'] || $el['is_media_candidate'] || $el['is_interactive_candidate']) {
                $candidateElements[] = [
                    'tag' => $el['tag'],
                    'dom_path' => $el['dom_path'],
                    'selector' => $el['selector'],
                    'text_content' => $el['text_content'],
                    'direct_text' => $el['direct_text'],
                    'attributes' => array_intersect_key($el['attributes'], array_flip(['id', 'class', 'src', 'href', 'alt', 'role', 'aria-label', 'data-type'])),
                    'element_fingerprint' => $el['element_fingerprint'],
                    'inline_styles' => $el['inline_styles'],
                ];
            }
        }

        return [
            'task' => 'Analyze the provided e-commerce banner DOM structure, typography, and assets to classify semantic roles for dynamic content fields.',
            'taxonomy' => BannerEngineManager::getSemanticRoles(),
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'import_source' => $template->import_source,
            ],
            'candidates' => $candidateElements,
            'css_summary' => [
                'rules_count' => $cssData['total_rules'],
                'media_queries' => array_column($cssData['media_queries'], 'condition'),
                'keyframes' => array_column($cssData['keyframes'], 'name'),
            ],
            'assets' => array_keys($assetManifest),
        ];
    }

    /**
     * Determine if external AI API is configured.
     *
     * @return bool
     */
    protected function isAiConfigured(): bool
    {
        return !empty(env('BANNER_AI_API_KEY')) || !empty(config('services.banner_ai.api_key'));
    }

    /**
     * Call AI API endpoint.
     *
     * @param array $promptContext
     * @param BannerTemplate $template
     * @return array
     */
    protected function callAiApi(array $promptContext, BannerTemplate $template): array
    {
        $apiKey = env('BANNER_AI_API_KEY') ?: config('services.banner_ai.api_key');
        $endpoint = env('BANNER_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions');

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post($endpoint, [
                'model' => env('BANNER_AI_MODEL', 'gpt-4o'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert AI design layout and semantic classifier. Return a clean JSON object with an "elements" array containing predicted roles, labels, field types, confidence scores (0.0 to 1.0), editable flags, and reasons.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($promptContext),
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->successful()) {
            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '{}';
            return json_decode($content, true) ?: [];
        }

        throw new \Exception('AI API responded with status: ' . $response->status());
    }

    /**
     * Generate deterministic heuristic schema when external AI is offline.
     *
     * @param array $domData
     * @param array $cssData
     * @param array $viewportData
     * @return array
     */
    protected function generateHeuristicAiSchema(array $domData, array $cssData, array $viewportData): array
    {
        $elements = [];

        foreach ($domData['elements'] as $el) {
            $tag = $el['tag'];
            $text = trim($el['text_content']);
            $directText = trim($el['direct_text']);
            $attrs = $el['attributes'];

            $role = null;
            $label = '';
            $fieldType = 'text';
            $defaultValue = $text;
            $isEditable = true;
            $confidence = 0.80;
            $reason = '';

            // 1. Price & Discount Detection
            if ($el['is_text_candidate'] && !($tag === 'p' && mb_strlen($text) > 30) && preg_match('/(?:[\$€£₹¥]|USD|INR|EUR)\s*[\d,]+(?:\.\d{2})?|\b\d+\s*%/i', $text)) {
                if (preg_match('/\b\d+\s*%/i', $text) && (mb_strlen($text) <= 30 || preg_match('/\b(?:off|discount|save|sale)\b/i', $text))) {
                    $role = 'discount';
                    $label = 'Discount Offer Badge';
                    $confidence = 0.94;
                    $reason = "Text contains discount percentage pattern ({$text})";
                } elseif (preg_match('/(?:[\$€£₹¥]|USD|INR|EUR)\s*[\d,]+(?:\.\d{2})?/i', $text)) {
                    $role = 'price';
                    $label = 'Product Selling Price';
                    $fieldType = 'price';
                    $confidence = 0.95;
                    $reason = "Currency symbol and price value detected ({$text})";
                }
            }

            // 2. Headings
            elseif (in_array($tag, ['h1', 'h2'], true) && !empty($text)) {
                $role = 'headline';
                $label = $tag === 'h1' ? 'Primary Hero Title' : 'Section Sub-Headline';
                $confidence = 0.97;
                $reason = "High-prominence <{$tag}> title heading";
            } elseif (in_array($tag, ['h3', 'h4', 'h5', 'h6'], true) && !empty($text)) {
                $role = 'subtitle';
                $label = 'Supporting Subtitle';
                $confidence = 0.91;
                $reason = "Secondary heading element <{$tag}>";
            }

            // 3. Interactive CTA Buttons
            elseif ($tag === 'button' || $el['is_interactive_candidate']) {
                $role = 'cta';
                $label = 'Call to Action Button';
                $fieldType = 'cta';
                $defaultValue = $text ?: ($attrs['aria-label'] ?? 'Shop Now');
                $confidence = 0.95;
                $reason = "Clickable action trigger <{$tag}>";
            }

            // 4. Images
            elseif ($tag === 'img') {
                $role = 'product_image';
                $label = !empty($attrs['alt']) ? 'Product Photo (' . $attrs['alt'] . ')' : 'Featured Product Image';
                $fieldType = 'image';
                $defaultValue = $el['src'] ?? '';
                $confidence = !empty($attrs['alt']) ? 0.93 : 0.86;
                $reason = "Product visual presentation <img src='{$defaultValue}'>";
            }

            // 5. Video
            elseif ($tag === 'video') {
                $role = 'video';
                $label = 'Hero Promotional Video';
                $fieldType = 'video';
                $defaultValue = $el['src'] ?? '';
                $confidence = 0.92;
                $reason = "HTML5 background/hero video media";
            }

            // 6. Badges / Eyebrows
            elseif ($el['is_text_candidate'] && in_array($tag, ['span', 'small', 'strong', 'b', 'label'], true) && mb_strlen($text) < 25 && preg_match('/^[A-Z0-9\s!%-]+$/', $text) && !empty($text)) {
                $role = 'badge';
                $label = 'Highlight Badge';
                $confidence = 0.89;
                $reason = "Uppercase short promotional badge ({$text})";
            }

            // 7. Paragraph Description
            elseif ($tag === 'p' && !empty($text)) {
                $role = 'description';
                $label = 'Marketing Description';
                $confidence = 0.90;
                $reason = "Descriptive copy paragraph";
            }

            // 8. 3D Canvas / Vector Decoration (Locked)
            elseif (in_array($tag, ['canvas', 'svg'], true)) {
                $role = $tag === 'canvas' ? 'animation' : 'decorative';
                $label = $tag === 'canvas' ? '3D / WebGL Canvas Layer' : 'Vector Decoration';
                $fieldType = 'unknown';
                $isEditable = false;
                $confidence = 0.96;
                $reason = "Visual background / rendering element (locked)";
            }

            if ($role) {
                $elements[] = [
                    'dom_path' => $el['dom_path'],
                    'selector' => $el['selector'],
                    'role' => $role,
                    'label' => $label,
                    'field_type' => $fieldType,
                    'default_value' => $defaultValue,
                    'confidence' => $confidence,
                    'reason' => $reason,
                    'editable' => $isEditable,
                    'text_fingerprint' => $el['text_fingerprint'],
                    'element_fingerprint' => $el['element_fingerprint'],
                ];
            }
        }

        return ['elements' => $elements];
    }

    /**
     * Synchronize BannerField records in database.
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

        $template->fields()->whereNotIn('field_key', $existingFieldKeys)->delete();
    }
}
