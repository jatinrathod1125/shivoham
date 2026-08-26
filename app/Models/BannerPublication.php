<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'version_id',
        'position',
        'target_audience',
        'cached_html',
        'cached_css',
        'is_active',
        'starts_at',
        'expires_at',
        'impressions_count',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'target_audience' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BannerVersion::class, 'version_id');
    }
}
