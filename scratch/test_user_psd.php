<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BannerEngine\Import\PsdImportService;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Http\UploadedFile;

$psdPath = 'c:/Users/jmrat/Desktop/shivoham/4021545.psd';

if (!file_exists($psdPath)) {
    echo "ERROR: File not found at {$psdPath}\n";
    exit(1);
}

$fileSize = filesize($psdPath);
echo "=== PSD FILE AUDIT ===\n";
echo "File Path: {$psdPath}\n";
echo "File Size: " . round($fileSize / 1048576, 2) . " MB (" . number_format($fileSize) . " bytes)\n\n";

$importer = new PsdImportService();

echo "1. Parsing PSD Binary Structure...\n";
try {
    $structure = $importer->parsePsdStructure($psdPath);
    echo "   Dimensions: {$structure['width']} x {$structure['height']} px\n";
    echo "   Color Mode: {$structure['color_mode']}\n";
    echo "   Depth: {$structure['depth']} bit\n";
    echo "   Channels: {$structure['channels']}\n";
    echo "   Layers Discovered: " . count($structure['layers']) . "\n\n";

    echo "=== DISCOVERED LAYERS ===\n";
    foreach ($structure['layers'] as $idx => $layer) {
        $type = $layer['is_text'] ? "TEXT" : "IMAGE/RASTER";
        $text = $layer['text_content'] ? " | Text: '{$layer['text_content']}'" : "";
        $pos = "({$layer['left']}, {$layer['top']}) {$layer['width']}x{$layer['height']}px";
        echo " - Layer [{$idx}] \"{$layer['name']}\" ({$type}) -> {$pos}{$text}\n";
    }

    echo "\n2. Running Full PSD Import Pipeline...\n";
    $uploadedFile = new UploadedFile($psdPath, '4021545.psd', 'image/vnd.adobe.photoshop', null, true);
    $template = $importer->importPsd($uploadedFile, [
        'name' => 'Photoshop PSD Banner Test',
    ]);

    echo "   Template Created ID: {$template->id}\n";
    echo "   Assets Extracted: " . $template->assets()->count() . "\n";
    echo "   HTML Length: " . strlen($template->raw_html) . " bytes\n";
    echo "   CSS Length: " . strlen($template->raw_css) . " bytes\n\n";

    echo "=== EXTRACTED DYNAMIC FIELDS ===\n";
    $fields = $template->fields()->get();
    echo "   Total Fields: " . $fields->count() . "\n";
    foreach ($fields as $f) {
        echo "   - [{$f->field_type}] {$f->label} (Role: {$f->semantic_role}, Confidence: " . round($f->confidence_score * 100) . "%) Default: '{$f->default_value}'\n";
    }

    echo "\n3. Testing Sandboxed Renderer...\n";
    $renderer = new SandboxedRenderer();
    $rendered = $renderer->render($template);
    echo "   Rendered Output Size: " . strlen($rendered) . " bytes\n";
    echo "   Status: SUCCESSFUL & READY FOR LIVE STOREFRONT!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
