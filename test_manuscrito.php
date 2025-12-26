<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

// Buscar PDF de prueba
$testDir = __DIR__ . '/assets/expedientes/expedientes_subidos';
$subdirs = glob($testDir . '/*', GLOB_ONLYDIR);
$pdfPath = null;

foreach ($subdirs as $subdir) {
    $pdfs = glob($subdir . '/*.pdf');
    if (!empty($pdfs)) {
        $pdfPath = $pdfs[0];
        break;
    }
}

if (!$pdfPath) {
    die('No se encontró PDF de prueba');
}

echo '<h1>Test Extracción Números Manuscritos</h1>';
echo '<p>Archivo: ' . basename($pdfPath) . '</p>';

// Configurar ImageMagick
$config = require __DIR__ . '/config/pdf.php';
$convertCmd = !empty($config['convert_path']) && file_exists($config['convert_path'])
    ? '\"' . $config['convert_path'] . '\"'
    : 'magick';

// Convertir primera página
$tempImage = sys_get_temp_dir() . '/test_cert.png';
$cmd = sprintf(
    '%s -density 200 %s[0] -colorspace Gray -quality 90 %s 2>&1',
    $convertCmd,
    escapeshellarg($pdfPath),
    escapeshellarg($tempImage)
);

exec($cmd, $output, $code);

if ($code !== 0 || !file_exists($tempImage)) {
    die('Error al convertir PDF');
}

list($width, $height) = getimagesize($tempImage);
echo '<p>Tamaño imagen: ' . $width . 'x' . $height . '</p>';

// Recortar esquina superior derecha (60% ancho, 15% alto)
$cropX = (int)($width * 0.60);
$cropY = 0;
$cropW = (int)($width * 0.40);
$cropH = (int)($height * 0.15);

$cropPath = sys_get_temp_dir() . '/test_crop.png';
$cropCmd = sprintf(
    '%s %s -crop %dx%d+%d+%d +repage %s 2>&1',
    $convertCmd,
    escapeshellarg($tempImage),
    $cropW, $cropH, $cropX, $cropY,
    escapeshellarg($cropPath)
);

exec($cropCmd);

if (file_exists($cropPath)) {
    echo '<h3>Región recortada (esquina superior derecha)</h3>';
    $img = base64_encode(file_get_contents($cropPath));
    echo '<img src=\"data:image/png;base64,' . $img . '\" style=\"border:2px solid #ccc;\">';
    
    // OCR
    $ocrFile = sys_get_temp_dir() . '/test_ocr';
    $ocrCmd = sprintf(
        'tesseract %s %s -l spa --psm 7 txt 2>&1',
        escapeshellarg($cropPath),
        escapeshellarg($ocrFile)
    );
    
    exec($ocrCmd);
    
    if (file_exists($ocrFile . '.txt')) {
        $text = file_get_contents($ocrFile . '.txt');
        echo '<h3>Texto extraído:</h3>';
        echo '<pre>' . htmlspecialchars($text) . '</pre>';
        unlink($ocrFile . '.txt');
    }
    
    unlink($cropPath);
}

unlink($tempImage);
?>
