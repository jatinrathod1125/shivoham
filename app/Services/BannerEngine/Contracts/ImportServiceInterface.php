<?php

namespace App\Services\BannerEngine\Contracts;

use App\Models\BannerTemplate;
use Illuminate\Http\UploadedFile;

interface ImportServiceInterface
{
    /**
     * Import a design package from a ZIP file.
     *
     * @param UploadedFile|string $file
     * @param array $options
     * @return BannerTemplate
     */
    public function importZip(UploadedFile|string $file, array $options = []): BannerTemplate;

    /**
     * Import raw HTML, CSS, and JS snippets.
     *
     * @param string $html
     * @param string|null $css
     * @param string|null $js
     * @param array $options
     * @return BannerTemplate
     */
    public function importRawCode(string $html, ?string $css = null, ?string $js = null, array $options = []): BannerTemplate;

    /**
     * Import a Photoshop (.PSD) document.
     *
     * @param UploadedFile|string $file
     * @param array $options
     * @return BannerTemplate
     */
    public function importPsd(UploadedFile|string $file, array $options = []): BannerTemplate;
}
