<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerAnalysis extends Model
{
    use HasFactory;

    public const ENGINE_DOM_HEURISTIC = 'dom_heuristic';
    public const ENGINE_MULTIMODAL_AI = 'multimodal_ai';
    public const ENGINE_OCR_VISION = 'ocr_vision';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'template_id',
        'analysis_engine',
        'status',
        'overall_confidence',
        'elements_detected_count',
        'editable_elements_count',
        'locked_elements_count',
        'raw_prompt',
        'raw_response',
        'detected_schema',
        'reviewer_overrides',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'overall_confidence' => 'float',
            'elements_detected_count' => 'integer',
            'editable_elements_count' => 'integer',
            'locked_elements_count' => 'integer',
            'detected_schema' => 'array',
            'reviewer_overrides' => 'array',
        ];
    }

    public function getConfidenceTierAttribute(): string
    {
        return \App\Services\BannerEngine\BannerEngineManager::classifyConfidence($this->overall_confidence);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'template_id');
    }
}
