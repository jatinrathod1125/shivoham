<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Banner::with('template')->get() as $b) {
    echo "ID: {$b->id} | Title: {$b->title} | Template: " . ($b->template ? "ID {$b->template->id}" : "None") . "\n";
    if ($b->template) {
        echo "   HTML length: " . strlen($b->template->raw_html) . " | CSS length: " . strlen($b->template->raw_css) . "\n";
    }
}
