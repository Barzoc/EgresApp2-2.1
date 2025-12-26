<?php
if (class_exists('ZipArchive')) {
    echo "ZipArchive is available.\n";
    echo "Extension version: " . phpversion('zip') . "\n";
} else {
    echo "Error: ZipArchive class not found.\n";
}
echo "PHP INI Path: " . php_ini_loaded_file() . "\n";
?>