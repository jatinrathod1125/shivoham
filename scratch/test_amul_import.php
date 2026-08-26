<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Banner;
use App\Models\BannerVersion;
use App\Services\BannerEngine\Analyzer\StructuralAnalysisEngine;
use App\Services\BannerEngine\Import\ZipImportService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Http\UploadedFile;

$zipPath = base_path('public/amul_milk_hero_banner.zip');
$file = new UploadedFile($zipPath, 'amul_milk_hero_banner.zip', 'application/zip', null, true);

$importer = new ZipImportService();
$template = $importer->importZip($file, ['name' => 'Amul Taaza Hero Banner']);

$analyzer = new StructuralAnalysisEngine();
$analysis = $analyzer->analyze($template);

$fields = $template->fields()->get();

echo "IMPORT SUCCESSFUL!\n";
echo "Template ID: " . $template->id . "\n";
echo "Assets Discovered: " . $template->assets()->count() . "\n";
echo "Fields Extracted: " . $fields->count() . "\n";

foreach ($fields as $f) {
    echo " - [{$f->field_type}] {$f->label} (Role: {$f->semantic_role}, Confidence: " . round($f->confidence_score * 100) . "%)\n";
}

$renderer = new SandboxedRenderer();
$rendered = $renderer->render($template);
echo "Rendered Length: " . strlen($rendered) . " bytes\n";
