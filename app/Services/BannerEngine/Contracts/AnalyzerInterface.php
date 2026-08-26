<?php

namespace App\Services\BannerEngine\Contracts;

use App\Models\BannerAnalysis;
use App\Models\BannerTemplate;

interface AnalyzerInterface
{
    /**
     * Analyze a template structure and extract semantic elements.
     *
     * @param BannerTemplate $template
     * @param array $options
     * @return BannerAnalysis
     */
    public function analyze(BannerTemplate $template, array $options = []): BannerAnalysis;
}
