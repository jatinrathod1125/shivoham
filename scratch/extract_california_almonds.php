<?php

$zipPath = 'c:/Users/jmrat/Desktop/shivoham/california_almonds_hero_banner.zip';
$extractDir = 'c:/Users/jmrat/Desktop/california_almonds_source';

$zip = new ZipArchive();
if ($zip->open($zipPath) === true) {
    $zip->extractTo($extractDir);
    $zip->close();
    echo "EXTRACTED_SUCCESS: $extractDir\n";
} else {
    echo "EXTRACT_FAILED\n";
}
