<?php

$iniFiles = array_merge(
    glob('C:/wamp64/bin/php/php8.*/*.ini') ?: [],
    glob('C:/wamp64/bin/apache/apache*/bin/php.ini') ?: []
);

echo "Found INI files:\n";
foreach ($iniFiles as $ini) {
    echo " - " . $ini . "\n";
    if (is_writable($ini)) {
        $content = file_get_contents($ini);
        
        // Replace post_max_size
        $content = preg_replace('/^(\s*post_max_size\s*=\s*).*/m', '$1' . '512M', $content);
        
        // Replace upload_max_filesize
        $content = preg_replace('/^(\s*upload_max_filesize\s*=\s*).*/m', '$1' . '512M', $content);
        
        // Replace memory_limit
        $content = preg_replace('/^(\s*memory_limit\s*=\s*).*/m', '$1' . '1024M', $content);

        // Replace max_execution_time
        $content = preg_replace('/^(\s*max_execution_time\s*=\s*).*/m', '$1' . '300', $content);

        // Replace max_input_time
        $content = preg_replace('/^(\s*max_input_time\s*=\s*).*/m', '$1' . '300', $content);

        file_put_contents($ini, $content);
        echo "   -> UPDATED successfully to 512M / 1024M!\n";
    } else {
        echo "   -> NOT writable\n";
    }
}
