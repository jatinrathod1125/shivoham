<?php

namespace App\Services\BannerEngine\Contracts;

interface SanitizerInterface
{
    /**
     * Sanitize raw HTML markup.
     *
     * @param string $html
     * @return string
     */
    public function sanitizeHtml(string $html): string;

    /**
     * Sanitize raw CSS stylesheet content.
     *
     * @param string $css
     * @return string
     */
    public function sanitizeCss(string $css): string;

    /**
     * Sanitize SVG markup.
     *
     * @param string $svg
     * @return string
     */
    public function sanitizeSvg(string $svg): string;
}
