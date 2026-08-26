<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Universal AI Banner Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized settings for import limits, confidence scoring thresholds,
    | security sandbox CSP headers, asset storage, and AI analysis pipelines.
    |
    */

    'storage_disk' => env('BANNER_ENGINE_DISK', 'public'),
    'storage_path' => env('BANNER_ENGINE_PATH', 'banner_engine'),

    'limits' => [
        'max_zip_size_kb' => env('BANNER_MAX_ZIP_KB', 512000), // 500MB
        'max_psd_size_kb' => env('BANNER_MAX_PSD_KB', 512000), // 500MB
        'max_extracted_files' => env('BANNER_MAX_EXTRACTED_FILES', 500),
        'max_html_size_kb' => env('BANNER_MAX_HTML_KB', 10240), // 10MB
        'max_css_size_kb' => env('BANNER_MAX_CSS_KB', 10240),
        'max_image_size_kb' => env('BANNER_MAX_IMAGE_KB', 51200), // 50MB
        'max_video_size_kb' => env('BANNER_MAX_VIDEO_KB', 102400), // 100MB
    ],

    'confidence_thresholds' => [
        'auto_accept' => 0.90,
        'review_recommended' => 0.75,
        'needs_review' => 0.50,
    ],

    'allowed_mime_types' => [
        'html' => ['text/html', 'application/xhtml+xml', 'text/plain'],
        'css' => ['text/css', 'text/plain'],
        'js' => ['application/javascript', 'text/javascript', 'application/x-javascript'],
        'image' => ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/svg+xml', 'image/gif'],
        'psd' => ['image/vnd.adobe.photoshop', 'application/x-photoshop', 'image/photoshop', 'image/x-photoshop', 'application/photoshop', 'application/psd', 'image/psd', 'application/octet-stream'],
        'video' => ['video/mp4', 'video/webm', 'video/ogg'],
        'font' => ['font/woff', 'font/woff2', 'font/ttf', 'font/otf', 'application/font-woff', 'application/font-woff2', 'application/x-font-ttf'],
        'archive' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
    ],

    'security' => [
        'enable_iframe_sandbox' => true,
        'csp_header' => "default-src 'self' data: blob: https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: blob: https:; media-src 'self' data: blob: https:; frame-ancestors 'self';",
        'strip_dangerous_tags' => ['script', 'iframe', 'object', 'embed', 'applet', 'meta', 'link[rel="import"]'],
        'strip_dangerous_attributes' => ['onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur', 'formaction'],
    ],

    'semantic_roles' => [
        'headline' => ['label' => 'Headline / Main Title', 'editable' => true, 'type' => 'text'],
        'subtitle' => ['label' => 'Subtitle', 'editable' => true, 'type' => 'text'],
        'description' => ['label' => 'Description / Body Copy', 'editable' => true, 'type' => 'text'],
        'eyebrow' => ['label' => 'Eyebrow / Category Tag', 'editable' => true, 'type' => 'text'],
        'offer' => ['label' => 'Promotional Offer', 'editable' => true, 'type' => 'text'],
        'discount' => ['label' => 'Discount Badge', 'editable' => true, 'type' => 'text'],
        'price' => ['label' => 'Selling Price', 'editable' => true, 'type' => 'price'],
        'old_price' => ['label' => 'Original / Strikethrough Price', 'editable' => true, 'type' => 'price'],
        'currency' => ['label' => 'Currency Symbol', 'editable' => true, 'type' => 'text'],
        'cta' => ['label' => 'Call to Action Button', 'editable' => true, 'type' => 'cta'],
        'product_image' => ['label' => 'Featured Product Image', 'editable' => true, 'type' => 'image'],
        'product' => ['label' => 'Product Name / Descriptor', 'editable' => true, 'type' => 'product'],
        'logo' => ['label' => 'Brand / Store Logo', 'editable' => true, 'type' => 'image'],
        'badge' => ['label' => 'Feature Badge', 'editable' => true, 'type' => 'text'],
        'category' => ['label' => 'Category Label', 'editable' => true, 'type' => 'text'],
        'brand' => ['label' => 'Brand Label', 'editable' => true, 'type' => 'text'],
        'rating' => ['label' => 'Rating Snippet', 'editable' => true, 'type' => 'text'],
        'timer' => ['label' => 'Countdown Timer Target', 'editable' => true, 'type' => 'date'],
        'date' => ['label' => 'Validity Date', 'editable' => true, 'type' => 'date'],
        'background' => ['label' => 'Background Layer', 'editable' => false, 'type' => 'image'],
        'decorative' => ['label' => 'Decorative Shape / Accent', 'editable' => false, 'type' => 'unknown'],
        'video' => ['label' => 'Video Background / Media', 'editable' => true, 'type' => 'video'],
        'animation' => ['label' => 'Canvas / Animation Layer', 'editable' => false, 'type' => 'unknown'],
        'unknown' => ['label' => 'Unknown Element', 'editable' => false, 'type' => 'unknown'],
    ],

    'viewports' => [
        'desktop' => ['width' => 1440, 'height' => 600, 'label' => 'Desktop (1440px)'],
        'tablet' => ['width' => 768, 'height' => 500, 'label' => 'Tablet (768px)'],
        'mobile' => ['width' => 375, 'height' => 450, 'label' => 'Mobile (375px)'],
    ],

    'cache' => [
        'enabled' => env('BANNER_CACHE_ENABLED', true),
        'ttl_seconds' => env('BANNER_CACHE_TTL', 3600),
    ],
];
