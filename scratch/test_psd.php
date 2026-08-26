<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BannerEngine\Import\PsdImportService;

// Create test mock
$tempPath = tempnam(sys_get_temp_dir(), 'test_psd_') . '.psd';
$handle = fopen($tempPath, 'wb');

// 1. Header (26 bytes)
fwrite($handle, '8BPS'); // Signature
fwrite($handle, pack('n', 1)); // Version 1
fwrite($handle, str_repeat("\x00", 6)); // Reserved
fwrite($handle, pack('n', 3)); // Channels: 3 (RGB)
fwrite($handle, pack('N', 600)); // Height
fwrite($handle, pack('N', 1200)); // Width
fwrite($handle, pack('n', 8)); // Depth: 8-bit
fwrite($handle, pack('n', 3)); // ColorMode: RGB

// 2. Color Mode Data
fwrite($handle, pack('N', 0));

// 3. Image Resources
fwrite($handle, pack('N', 0));

// 4. Layer Section
fwrite($handle, pack('N', 0)); // Length 0 for layer section

// 5. Image data
fwrite($handle, pack('n', 0));
fclose($handle);

$importer = new PsdImportService();
$res = $importer->parsePsdStructure($tempPath);
var_dump($res);
unlink($tempPath);
