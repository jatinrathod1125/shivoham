<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerField extends Model
{
    use HasFactory;

    public const CONFIDENCE_AUTO_ACCEPT = 'auto_accept';
    public const CONFIDENCE_REVIEW_RECOMMENDED = 'review_recommended';
    public const CONFIDENCE_NEEDS_REVIEW = 'needs_review';
    public const CONFIDENCE_UNKNOWN = 'unknown';

    protected $fillable = [
        'template_id',
        'field_key',
        'semantic_role',
        'label',
        'field_type',
        'default_value',
        'dom_path',
        'selector',
        'text_fingerprint',
        'element_fingerprint',
        'confidence_score',
        'confidence_status',
        'detection_reason',
        'is_editable',
        'is_locked',
        'validation_rules',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'is_editable' => 'boolean',
            'is_locked' => 'boolean',
            'validation_rules' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'template_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(BannerFieldMapping::class, 'banner_field_id');
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence_score >= config('banner_engine.confidence_thresholds.auto_accept', 0.90);
    }
}
