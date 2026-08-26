<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerFieldMapping extends Model
{
    use HasFactory;

    public const TYPE_STATIC = 'static';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_BRAND = 'brand';
    public const TYPE_OFFER = 'offer';

    protected $fillable = [
        'banner_field_id',
        'banner_version_id',
        'mapping_type',
        'static_value',
        'product_id',
        'product_attribute',
        'fallback_value',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(BannerField::class, 'banner_field_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BannerVersion::class, 'banner_version_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
