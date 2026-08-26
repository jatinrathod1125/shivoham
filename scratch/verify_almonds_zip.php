<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Import\ZipImportService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Http\UploadedFile;

$zipPath = 'c:/Users/jmrat/Desktop/shivoham/california_almonds_hero_banner.zip';
$file = new UploadedFile($zipPath, 'california_almonds_hero_banner.zip', 'application/zip', null, true);

$importer = new ZipImportService();
$template = $importer->importZip($file, ['name' => 'California Almonds Hero Banner']);

$analyzer = new StructuralAnalysisEngine();
$analysis = $analyzer->analyze($template);

$fields = $template->fields()->get();

echo "IMPORT TEST PASSED!\n";
echo "Template ID: " . $template->id . "\n";
echo "Assets Count: " . $template->assets()->count() . "\n";
echo "Extracted Fields: " . $fields->count() . "\n";

foreach ($fields as $f) {
    echo " - [{$f->field_type}] {$f->label} (Role: {$f->semantic_role}, Confidence: " . round($f->confidence_score * 100) . "%)\n";
}
