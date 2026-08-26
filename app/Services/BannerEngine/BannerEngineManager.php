<?php

namespace App\Services\BannerEngine;

use App\Models\Banner;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use Illuminate\Support\Facades\Config;

class BannerEngineManager
{
    /**
     * Get confidence classification based on numeric score.
     *
     * @param float $score
     * @return string
     */
    public static function classifyConfidence(float $score): string
    {
        $thresholds = Config::get('banner_engine.confidence_thresholds', [
            'auto_accept' => 0.90,
            'review_recommended' => 0.75,
            'needs_review' => 0.50,
        ]);

        if ($score >= $thresholds['auto_accept']) {
            return 'auto_accept';
        }

        if ($score >= $thresholds['review_recommended']) {
            return 'review_recommended';
        }

        if ($score >= $thresholds['needs_review']) {
            return 'needs_review';
        }

        return 'unknown';
    }

    /**
     * Get all supported semantic role definitions.
     *
     * @return array
     */
    public static function getSemanticRoles(): array
    {
        return Config::get('banner_engine.semantic_roles', []);
    }

    /**
     * Generate standard sandbox CSP header.
     *
     * @return string
     */
    public static function getCspHeader(): string
    {
        return Config::get('banner_engine.security.csp_header', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
    }
}
