<?php

namespace App\Services\BannerEngine\Catalog;

use App\Models\BannerField;
use App\Models\BannerFieldMapping;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductBindingResolver
{
    /**
     * Resolve all field values for a template version, merging static inputs and live catalog bindings.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $versionOrValues
     * @return array
     */
    public function resolveFieldValues(BannerTemplate $template, BannerVersion|array|null $versionOrValues = null): array
    {
        $rawValues = [];
        $mappings = collect();

        if ($versionOrValues instanceof BannerVersion) {
            $rawValues = $versionOrValues->field_values ?? [];
            $mappings = $versionOrValues->mappings()->with(['product', 'field'])->get();
        } elseif (is_array($versionOrValues)) {
            $rawValues = $versionOrValues;
        }

        $fields = $template->fields()->get();
        $resolved = [];

        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            $mapping = $mappings->firstWhere('banner_field_id', $field->id);

            if ($mapping && $mapping->mapping_type !== BannerFieldMapping::TYPE_STATIC) {
                $resolved[$fieldKey] = $this->resolveCatalogAttribute($mapping, $field);
            } elseif (isset($rawValues[$fieldKey])) {
                $resolved[$fieldKey] = $rawValues[$fieldKey];
            } elseif (isset($rawValues[$field->semantic_role])) {
                $resolved[$fieldKey] = $rawValues[$field->semantic_role];
            } else {
                $resolved[$fieldKey] = $field->default_value;
            }
        }

        return $resolved;
    }

    /**
     * Resolve a single dynamic catalog attribute value.
     *
     * @param BannerFieldMapping $mapping
     * @param BannerField $field
     * @return mixed
     */
    public function resolveCatalogAttribute(BannerFieldMapping $mapping, BannerField $field): mixed
    {
        if ($mapping->mapping_type === BannerFieldMapping::TYPE_PRODUCT && $mapping->product_id) {
            $product = $mapping->product ?? Product::find($mapping->product_id);
            if ($product) {
                return $this->extractProductAttribute($product, $mapping->product_attribute, $field);
            }
        }

        return $mapping->fallback_value ?: $field->default_value;
    }

    /**
     * Extract formatted product attribute value.
     *
     * @param Product $product
     * @param string|null $attribute
     * @param BannerField $field
     * @return mixed
     */
    public function extractProductAttribute(Product $product, ?string $attribute, BannerField $field): mixed
    {
        $attr = $attribute ?: $this->guessAttributeForRole($field->semantic_role);

        switch ($attr) {
            case 'name':
            case 'title':
                return $product->name;

            case 'short_description':
                return $product->short_description ?: $product->description;

            case 'description':
                return $product->description ?: $product->short_description;

            case 'price':
            case 'selling_price':
                $price = $product->special_price ?: $product->selling_price;
                return '$' . number_format($price, 2);

            case 'original_price':
            case 'cost_price':
                return '$' . number_format($product->selling_price ?: $product->cost_price, 2);

            case 'discount_percentage':
            case 'discount':
                if ($product->special_price && $product->selling_price > $product->special_price) {
                    $pct = round((($product->selling_price - $product->special_price) / $product->selling_price) * 100);
                    return "{$pct}% OFF";
                }
                return 'SPECIAL DEAL';

            case 'primary_image':
            case 'thumbnail':
            case 'image':
                $img = $product->thumbnail ?: ($product->images[0] ?? '/storage/products/placeholder.png');
                if ($field->field_type === 'image') {
                    return [
                        'url' => $img,
                        'alt' => $product->name,
                    ];
                }
                return $img;

            case 'stock_status':
            case 'availability':
                if ($product->stock_quantity <= 0) {
                    return 'Out of Stock';
                } elseif ($product->stock_quantity <= 5) {
                    return "Only {$product->stock_quantity} Left!";
                }
                return 'In Stock';

            case 'checkout_url':
            case 'url':
            case 'cta':
                $url = url('/products/' . ($product->slug ?: $product->id));
                if ($field->field_type === 'cta') {
                    return [
                        'text' => 'Shop ' . $product->name,
                        'url' => $url,
                    ];
                }
                return $url;

            default:
                return $product->getAttribute($attr) ?? $field->default_value;
        }
    }

    /**
     * Guess product attribute based on semantic role.
     *
     * @param string $role
     * @return string
     */
    protected function guessAttributeForRole(string $role): string
    {
        return match ($role) {
            'headline', 'product' => 'name',
            'subtitle', 'description' => 'short_description',
            'price' => 'price',
            'old_price' => 'original_price',
            'discount', 'offer' => 'discount_percentage',
            'product_image' => 'primary_image',
            'cta' => 'checkout_url',
            default => 'name',
        };
    }
}
