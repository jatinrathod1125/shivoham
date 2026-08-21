<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'cost_price',
        'selling_price',
        'special_price',
        'special_price_start',
        'special_price_end',
        'stock_quantity',
        'min_stock_threshold',
        'unit',
        'weight',
        'thumbnail',
        'images',
        'is_featured',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'special_price' => 'decimal:2',
            'special_price_start' => 'datetime',
            'special_price_end' => 'datetime',
            'stock_quantity' => 'integer',
            'min_stock_threshold' => 'integer',
            'weight' => 'decimal:2',
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    /**
     * Determine active selling price (considers special promotion timing)
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->special_price !== null && $this->special_price > 0) {
            $now = now();
            $startOk = $this->special_price_start === null || $now->gte($this->special_price_start);
            $endOk = $this->special_price_end === null || $now->lte($this->special_price_end);

            if ($startOk && $endOk) {
                return (float) $this->special_price;
            }
        }

        return (float) $this->selling_price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->effective_price < (float) $this->selling_price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock_threshold;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) return 'out_of_stock';
        if ($this->is_low_stock) return 'low_stock';
        return 'in_stock';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_threshold')
            ->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }
}
