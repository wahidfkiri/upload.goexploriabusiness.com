<?php
echo "=== PHP Limits ===\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . " seconds\n";
echo "max_input_time: " . ini_get('max_input_time') . " seconds\n";

echo "\n=== Server Configuration ===\n";
if (function_exists('apache_get_modules')) {
    echo "Apache modules: " . implode(', ', apache_get_modules()) . "\n";
}

echo "\n=== PHP Version ===\n";
echo phpversion() . "\n";