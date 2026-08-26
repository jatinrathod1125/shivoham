<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerVersion extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'banner_id',
        'template_id',
        'version_number',
        'status',
        'field_values',
        'template_snapshot',
        'change_summary',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'field_values' => 'array',
            'template_snapshot' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'template_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(BannerFieldMapping::class, 'banner_version_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(BannerPublication::class, 'version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
