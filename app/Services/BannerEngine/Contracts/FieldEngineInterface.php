<?php

namespace App\Services\BannerEngine\Contracts;

use App\Models\BannerField;
use App\Models\BannerTemplate;
use Illuminate\Support\Collection;

interface FieldEngineInterface
{
    /**
     * Extract dynamic fields from analyzed template schema.
     *
     * @param BannerTemplate $template
     * @param array $schema
     * @return Collection<int, BannerField>
     */
    public function syncFieldsFromSchema(BannerTemplate $template, array $schema): Collection;

    /**
     * Generate unique deterministic field ID.
     *
     * @param string $role
     * @param string $domPath
     * @return string
     */
    public function generateFieldKey(string $role, string $domPath): string;
}
