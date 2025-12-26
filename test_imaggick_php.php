<?php
// Test: Manually test ImageMagick execution from PHP

echo "=== Testing ImageMagick from PHP ===\n\n";

// Test 1: Check if 'magick' is in PATH
echo "Test 1: Checking if 'magick' command is available...\n";
exec('magick --version 2>&1', $output1, $returnCode1);
echo "Return code: $returnCode1\n";
echo "Output:\n" . implode("\n", $output1) . "\n\n";

// Test 2: Try to convert a PDF page
echo "Test 2: Trying to convert a PDF page to PNG...\n";
$pdfPath = 'C:/laragon/www/EGRESAPP2/assets/expedientes/expedientes_subidos/NOELIA_ANDREA_DUBO_PIZARRO___000006_2.pdf';
$outputImage = sys_get_temp_dir() . '/test_page.png';

$command = sprintf(
    'magick -density 300 "%s[0]" -background white -alpha remove -colorspace Gray -quality 100 "%s" 2>&1',
    $pdfPath,
    $outputImage
);

echo "Command: $command\n";
exec($command, $output2, $returnCode2);
echo "Return code: $returnCode2\n";
echo "Output:\n" . implode("\n", $output2) . "\n";

if (file_exists($outputImage)) {
    echo "SUCCESS: Image created at: $outputImage\n";
    echo "File size: " . filesize($outputImage) . " bytes\n";
    @unlink($outputImage);
} else {
    echo "FAILED: Image was not created\n";
}

echo "\n=== PHP Environment Info ===\n";
echo "PHP_OS_FAMILY: " . PHP_OS_FAMILY . "\n";
echo "PATH: " . getenv('PATH') . "\n";
?>