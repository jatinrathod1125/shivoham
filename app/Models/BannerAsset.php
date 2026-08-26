<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BannerAsset extends Model
{
    use HasFactory;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_FONT = 'font';
    public const TYPE_STYLESHEET = 'stylesheet';
    public const TYPE_SCRIPT = 'script';
    public const TYPE_MODEL = 'model';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'template_id',
        'original_filename',
        'stored_path',
        'url',
        'mime_type',
        'file_size',
        'file_hash',
        'asset_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'template_id');
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->url && (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://'))) {
            return $this->url;
        }

        $disk = config('banner_engine.storage_disk', 'public');
        return Storage::disk($disk)->url($this->stored_path);
    }
}
