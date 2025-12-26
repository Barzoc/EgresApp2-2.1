<?php
/**
 * Script de diagnóstico para problemas de subida de expedientes
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico de Subida de Expedientes</h1>";
echo "<hr>";

// 1. Verificar configuración de PHP para subida de archivos
echo "<h2>1. Configuración de PHP para Subida de Archivos</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Configuración</th><th>Valor</th><th>Estado</th></tr>";

$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$memory_limit = ini_get('memory_limit');
$max_execution = ini_get('max_execution_time');
$file_uploads = ini_get('file_uploads');

echo "<tr><td>file_uploads</td><td>$file_uploads</td><td>" . ($file_uploads ? '✓ Habilitado' : '✗ DESHABILITADO') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>$upload_max</td><td>" . (parseSize($upload_max) >= 10485760 ? '✓ OK (≥10MB)' : '⚠ Pequeño') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>$post_max</td><td>" . (parseSize($post_max) >= 10485760 ? '✓ OK (≥10MB)' : '⚠ Pequeño') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>$memory_limit</td><td>" . (parseSize($memory_limit) >= 134217728 ? '✓ OK (≥128MB)' : '⚠ Bajo') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>{$max_execution}s</td><td>" . ($max_execution >= 60 ? '✓ OK' : '⚠ Corto') . "</td></tr>";
echo "</table>";

// 2. Verificar extensiones necesarias
echo "<h2>2. Extensiones PHP Necesarias</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Extensión</th><th>Estado</th></tr>";

$extensions = [
    'zip' => 'Manejo de archivos ZIP (requerido para LibreOffice)',
    'gd' => 'Procesamiento de imágenes',
    'imagick' => 'Procesamiento avanzado de imágenes (opcional)',
    'fileinfo' => 'Detección de tipos MIME'
];

foreach ($extensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    echo "<tr><td>$ext<br><small>$desc</small></td><td>" . ($loaded ? '✓ Instalada' : '✗ NO INSTALADA') . "</td></tr>";
}
echo "</table>";

// 3. Verificar permisos de directorios
echo "<h2>3. Permisos de Directorios</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Directorio</th><th>Existe</th><th>Lectura</th><th>Escritura</th></tr>";

$dirs = [
    __DIR__ . '/assets/expedientes',
    __DIR__ . '/assets/expedientes/expedientes_subidos',
    __DIR__ . '/assets/certificados',
    sys_get_temp_dir()
];

foreach ($dirs as $dir) {
    $exists = is_dir($dir);
    $readable = $exists && is_readable($dir);
    $writable = $exists && is_writable($dir);
    
    echo "<tr>";
    echo "<td>" . basename($dir) . "<br><small>$dir</small></td>";
    echo "<td>" . ($exists ? '✓' : '✗') . "</td>";
    echo "<td>" . ($readable ? '✓' : '✗') . "</td>";
    echo "<td>" . ($writable ? '✓' : '✗') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Verificar dependencias externas
echo "<h2>4. Dependencias Externas</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Herramienta</th><th>Estado</th><th>Ruta</th></tr>";

// Tesseract OCR
$tesseract = null;
exec('where tesseract 2>nul', $tesseract_output, $tesseract_code);
$tesseract = $tesseract_code === 0 ? implode("\n", $tesseract_output) : null;

echo "<tr><td>Tesseract OCR</td><td>" . ($tesseract ? '✓ Encontrado' : '✗ NO encontrado') . "</td><td>" . ($tesseract ?: 'N/A') . "</td></tr>";

// ImageMagick
$imagemagick = null;
exec('where magick 2>nul', $magick_output, $magick_code);
if ($magick_code !== 0) {
    exec('where convert 2>nul', $magick_output, $magick_code);
}
$imagemagick = $magick_code === 0 ? implode("\n", $magick_output) : null;

echo "<tr><td>ImageMagick</td><td>" . ($imagemagick ? '✓ Encontrado' : '✗ NO encontrado') . "</td><td>" . ($imagemagick ?: 'N/A') . "</td></tr>";

// LibreOffice
$libreoffice = null;
exec('where soffice 2>nul', $libre_output, $libre_code);
$libreoffice = $libre_code === 0 ? implode("\n", $libre_output) : null;

echo "<tr><td>LibreOffice</td><td>" . ($libreoffice ? '✓ Encontrado' : '✗ NO encontrado') . "</td><td>" . ($libreoffice ?: 'N/A') . "</td></tr>";

echo "</table>";

// 5. Test de escritura
echo "<h2>5. Test de Escritura</h2>";
$testFile = __DIR__ . '/assets/expedientes/expedientes_subidos/test_' . time() . '.txt';
$testContent = "Test de escritura - " . date('Y-m-d H:i:s');
$writeSuccess = @file_put_contents($testFile, $testContent);

if ($writeSuccess) {
    echo "<p style='color:green'>✓ Se pudo escribir en el directorio de expedientes</p>";
    echo "<p>Archivo de prueba: " . basename($testFile) . "</p>";
    @unlink($testFile);
} else {
    echo "<p style='color:red'>✗ NO se pudo escribir en el directorio de expedientes</p>";
    echo "<p>Error: " . error_get_last()['message'] ?? 'Desconocido' . "</p>";
}

// 6. Verificar archivos de configuración
echo "<h2>6. Archivos de Configuración</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Archivo</th><th>Existe</th></tr>";

$configFiles = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/config/google_drive.php',
];

foreach ($configFiles as $file) {
    $exists = file_exists($file);
    echo "<tr><td>" . basename(dirname($file)) . '/' . basename($file) . "</td><td>" . ($exists ? '✓' : '✗') . "</td></tr>";
}
echo "</table>";

// 7. Resumen
echo "<h2>7. Resumen</h2>";
$issues = [];

if (!ini_get('file_uploads')) {
    $issues[] = "file_uploads está deshabilitado en PHP";
}
if (!extension_loaded('zip')) {
    $issues[] = "La extensión ZIP no está instalada (necesaria para certificados)";
}
if (!extension_loaded('fileinfo')) {
    $issues[] = "La extensión fileinfo no está instalada";
}
if (!is_writable(__DIR__ . '/assets/expedientes/expedientes_subidos')) {
    $issues[] = "No hay permisos de escritura en el directorio de expedientes";
}
if (!$tesseract) {
    $issues[] = "Tesseract OCR no está instalado o no está en el PATH";
}

if (empty($issues)) {
    echo "<p style='color:green;font-size:18px;font-weight:bold'>✓ Todo parece estar correcto</p>";
} else {
    echo "<p style='color:red;font-size:18px;font-weight:bold'>Se encontraron los siguientes problemas:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li style='color:red'>$issue</li>";
    }
    echo "</ul>";
}

// Función auxiliar
function parseSize($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    }
    return round($size);
}
?>
