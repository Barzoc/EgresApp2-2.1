<?php
/**
 * Script de verificación rápida del sistema EGRESAPP2
 * Verifica que todas las herramientas estén disponibles y configuradas correctamente
 */

echo "=== VERIFICACIÓN RÁPIDA DE EGRESAPP2 ===" . PHP_EOL . PHP_EOL;

// 1. Verificar rutas configuradas
$config = [
    'Tesseract' => 'C:\Program Files\Tesseract-OCR\tesseract.exe',
    'ImageMagick' => 'C:\Program Files\ImageMagick-7.1.2-Q16-HDRI\magick.exe',
    'pdftotext' => 'C:\Program Files\poppler\Library\bin\pdftotext.exe',
    'Python' => 'C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe',
    'Service Account' => __DIR__ . '\config\hip-orbit-458817-b4-2b774255881e.json'
];

echo "1️⃣ ARCHIVOS CONFIGURADOS:" . PHP_EOL;
foreach ($config as $name => $path) {
    $exists = file_exists($path) ? '✓' : '✗';
    echo "  {$exists} {$name}: {$path}" . PHP_EOL;
}

echo PHP_EOL . "2️⃣ COMANDOS EN PATH:" . PHP_EOL;

// 2. Verificar comandos
$commands = [
    'tesseract' => 'tesseract --version 2>&1',
    'magick' => 'magick --version 2>&1',
    'pdftotext' => 'pdftotext -v 2>&1',
    'php' => 'php -v 2>&1'
];

foreach ($commands as $name => $cmd) {
    exec($cmd, $output, $code);
    $status = ($code === 0 || $code === 1) ? '✓' : '✗';
    echo "  {$status} {$name}" . PHP_EOL;
    unset($output);
}

echo PHP_EOL . "3️⃣ GOOGLE DRIVE:" . PHP_EOL;
try {
    require_once __DIR__ . '/lib/GoogleDriveClient.php';
    $driveClient = new GoogleDriveClient();

    if ($driveClient->isEnabled()) {
        echo "  ✓ Google Drive habilitado" . PHP_EOL;
        echo "  ✓ Configuración válida" . PHP_EOL;
    } else {
        echo "  ✗ Google Drive deshabilitado" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "4️⃣ DIRECTORIO DE EXPEDIENTES:" . PHP_EOL;
$expedientesDir = __DIR__ . '/assets/expedientes/expedientes_subidos';
if (!is_dir($expedientesDir)) {
    mkdir($expedientesDir, 0777, true);
    echo "  ✓ Directorio creado: {$expedientesDir}" . PHP_EOL;
} else {
    echo "  ✓ Directorio existe: {$expedientesDir}" . PHP_EOL;
}

// Verificar permisos
if (is_writable($expedientesDir)) {
    echo "  ✓ Directorio escribible" . PHP_EOL;
} else {
    echo "  ✗ Directorio NO escribible" . PHP_EOL;
    chmod($expedientesDir, 0777);
    echo "  ↻ Intentando corregir permisos..." . PHP_EOL;
}

echo PHP_EOL . "=== FIN DE VERIFICACIÓN ===" . PHP_EOL;
