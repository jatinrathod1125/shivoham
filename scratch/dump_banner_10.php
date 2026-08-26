<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$banner = App\Models\Banner::find(10);
if ($banner && $banner->template) {
    echo "=== HTML ===\n";
    echo $banner->template->raw_html . "\n";
    echo "=== CSS (first 500 chars) ===\n";
    echo substr($banner->template->raw_css, 0, 800) . "\n";
}
