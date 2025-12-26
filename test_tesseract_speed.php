<?php
require_once __DIR__ . '/lib/PDFProcessor.php';

// Hacer público el método para testearlo (hack sucio pero efectivo para test rápido)
// O mejor, copiar la lógica aquí.

$pdfPath = 'C:/Users/xerox/Desktop/EXPEDIENTES/LISTO PARA SUBIR/EDUARDO ANDRÉS GUERRERO TORRES.pdf';
$maxPages = 1;

echo "=== BENCHMARK TESSERACT 1 PAGE ===\n";
$start = microtime(true);

// 1. Convertir a imagen
echo "[1] Converting PDF page 0 to image...\n";
$firstImage = sys_get_temp_dir() . '/test_tess_' . uniqid() . '.png';
// Comando copiado de PDFProcessor::convertPDFToImages
$convertCmd = 'magick convert'; // Asumiendo magick
$command = sprintf(
    '%s -density 200 "%s[0]" -background white -alpha remove -alpha off -colorspace Gray -normalize -type Grayscale -quality 90 "%s" 2>&1',
    $convertCmd,
    $pdfPath,
    $firstImage
);
exec($command, $output, $code);

if (!file_exists($firstImage)) {
    echo "ERROR: Image conversion failed.\n";
    print_r($output);
    exit;
}
$timeImg = microtime(true) - $start;
echo "    Done in " . number_format($timeImg, 2) . "s\n";

// 2. Ejecutar Tesseract
echo "[2] Running Tesseract on image...\n";
$startOcr = microtime(true);
$tempTxt = sys_get_temp_dir() . '/test_tess_out_' . uniqid();
$tessCmd = sprintf('tesseract "%s" "%s" -l spa 2>&1', $firstImage, $tempTxt);
exec($tessCmd, $outputTess, $codeTess);

$timeOcr = microtime(true) - $startOcr;
echo "    Done in " . number_format($timeOcr, 2) . "s\n";

$fullTime = microtime(true) - $start;
echo "=== TOTAL TIME: " . number_format($fullTime, 2) . "s ===\n";

if (file_exists($tempTxt . '.txt')) {
    echo "TEXT EXTRACTED (first 100 chars): " . substr(file_get_contents($tempTxt . '.txt'), 0, 100) . "\n";
    unlink($tempTxt . '.txt');
}
unlink($firstImage);
