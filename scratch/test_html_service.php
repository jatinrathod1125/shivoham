<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BannerEngine\Import\HtmlImportService;

$service = new HtmlImportService();
echo "HTML SERVICE OK\n";
