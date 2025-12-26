<?php
// Test ImageMagick availability
echo "=== TEST IMAGEMAGICK ===\n\n";

// Test 1: Command execution
echo "1. Testing 'magick --version':\n";
$output = shell_exec('magick --version 2>&1');
echo $output ? $output : "FAILED: No output\n";
echo "\n";

// Test 2: Convert command
echo "2. Testing 'convert --version':\n";
$output = shell_exec('convert --version 2>&1');
echo $output ? $output : "FAILED: No output\n";
echo "\n";

// Test 3: PATH variable
echo "3. Current PATH:\n";
echo getenv('PATH') . "\n";
echo "\n";

// Test 4: Tesseract
echo "4. Testing 'tesseract --version':\n";
$output = shell_exec('tesseract --version 2>&1');
echo $output ? $output : "FAILED: No output\n";
