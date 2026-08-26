<?php

namespace App\Services\BannerEngine\FieldEngine;

use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Services\BannerEngine\BannerEngineManager;
use App\Services\BannerEngine\Contracts\FieldEngineInterface;
use Illuminate\Support\Collection;

class FieldExtractor implements FieldEngineInterface
{
    /**
     * Supported field types.
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_RICH_TEXT = 'rich_text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CTA = 'cta';
    public const TYPE_PRICE = 'price';
    public const TYPE_DISCOUNT = 'discount';
    public const TYPE_DATE = 'date';
    public const TYPE_TIMER = 'timer';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_BRAND = 'brand';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Extract dynamic fields from analyzed template schema.
     *
     * @param BannerTemplate $template
     * @param array $schema
     * @return Collection<int, BannerField>
     */
    public function syncFieldsFromSchema(BannerTemplate $template, array $schema): Collection
    {
        $elements = $schema['elements'] ?? $schema;
        $fieldModels = collect();
        $existingKeys = [];

        $sortOrder = 0;
        foreach ($elements as $el) {
            $domPath = $el['dom_path'] ?? '';
            $fingerprint = $el['element_fingerprint'] ?? '';
            $role = $el['semantic_role'] ?? ($el['role'] ?? 'unknown');

            $fieldKey = $el['field_key'] ?? $this->generateFieldKey($role, $domPath . '|' . $fingerprint);
            $confidence = floatval($el['confidence_score'] ?? ($el['confidence'] ?? 0.85));
            $isEditable = isset($el['is_editable']) ? (bool)$el['is_editable'] : (isset($el['editable']) ? (bool)$el['editable'] : true);

            $field = BannerField::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'field_key' => $fieldKey,
                ],
                [
                    'semantic_role' => $role,
                    'label' => $el['label'] ?? ucfirst(str_replace('_', ' ', $role)),
                    'field_type' => $this->normalizeFieldType($el['field_type'] ?? 'text', $role),
                    'default_value' => $el['default_value'] ?? ($el['value'] ?? ''),
                    'dom_path' => $domPath,
                    'selector' => $el['selector'] ?? '',
                    'text_fingerprint' => $el['text_fingerprint'] ?? null,
                    'element_fingerprint' => $fingerprint,
                    'confidence_score' => $confidence,
                    'confidence_status' => BannerEngineManager::classifyConfidence($confidence),
                    'detection_reason' => $el['detection_reason'] ?? ($el['reason'] ?? 'Dynamic field extraction'),
                    'is_editable' => $isEditable,
                    'is_locked' => !$isEditable,
                    'validation_rules' => $this->generateValidationRules($role, $el),
                    'sort_order' => $sortOrder++,
                ]
            );

            $fieldModels->push($field);
            $existingKeys[] = $fieldKey;
        }

        // Clean up unreferenced fields
        $template->fields()->whereNotIn('field_key', $existingKeys)->delete();

        return $fieldModels;
    }

    /**
     * Generate unique deterministic field ID.
     *
     * @param string $role
     * @param string $domPath
     * @return string
     */
    public function generateFieldKey(string $role, string $domPath): string
    {
        $hash = substr(hash('sha256', $role . '|' . $domPath), 0, 8);
        return 'fld_' . $hash;
    }

    /**
     * Normalize field type based on role and raw type.
     *
     * @param string $rawType
     * @param string $role
     * @return string
     */
    public function normalizeFieldType(string $rawType, string $role): string
    {
        $roleMap = [
            'headline' => self::TYPE_TEXT,
            'subtitle' => self::TYPE_TEXT,
            'description' => self::TYPE_TEXT,
            'eyebrow' => self::TYPE_TEXT,
            'offer' => self::TYPE_TEXT,
            'badge' => self::TYPE_TEXT,
            'price' => self::TYPE_PRICE,
            'old_price' => self::TYPE_PRICE,
            'discount' => self::TYPE_DISCOUNT,
            'cta' => self::TYPE_CTA,
            'product_image' => self::TYPE_IMAGE,
            'logo' => self::TYPE_IMAGE,
            'video' => self::TYPE_VIDEO,
            'date' => self::TYPE_DATE,
            'timer' => self::TYPE_TIMER,
            'product' => self::TYPE_PRODUCT,
            'category' => self::TYPE_CATEGORY,
            'brand' => self::TYPE_BRAND,
        ];

        return $roleMap[$role] ?? ($rawType ?: self::TYPE_TEXT);
    }

    /**
     * Generate automatic validation rules for field.
     *
     * @param string $role
     * @param array $elementData
     * @return array
     */
    protected function generateValidationRules(string $role, array $elementData): array
    {
        $rules = [
            'required' => false,
        ];

        switch ($role) {
            case 'headline':
                $rules['max_length'] = 150;
                break;
            case 'subtitle':
            case 'description':
                $rules['max_length'] = 300;
                break;
            case 'badge':
            case 'discount':
            case 'price':
                $rules['max_length'] = 50;
                break;
            case 'product_image':
            case 'logo':
                $rules['allowed_extensions'] = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
                break;
            case 'video':
                $rules['allowed_extensions'] = ['mp4', 'webm'];
                break;
        }

        return $rules;
    }
}
