<?php

namespace App\Services\BannerEngine\Contracts;

use App\Models\BannerTemplate;
use App\Models\BannerVersion;

interface RendererInterface
{
    /**
     * Render sandboxed HTML for preview or storefront injection.
     *
     * @param BannerTemplate $template
     * @param BannerVersion|array|null $values
     * @param array $options
     * @return string
     */
    public function render(BannerTemplate $template, BannerVersion|array|null $values = null, array $options = []): string;
}
