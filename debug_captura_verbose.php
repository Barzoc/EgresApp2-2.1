<?php
/**
 * Script de Depuración VERBOSA para Captura de Datos
 * Prueba cada método de extracción individualmente para ver dónde falla
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PDFProcessor.php';
$config = require __DIR__ . '/config/pdf.php';

echo "<h1>🕵️ Debugger de Captura de Datos Paso a Paso</h1>";

// 1. Verificar Configuración Cargada
echo "<h2>1. Verificación de Configuración (config/pdf.php)</h2>";
echo "<pre>";
echo "Usuario del Sistema detectado: " . get_current_user() . "\n";
echo "Ruta pdftotext configurada: " . ($config['pdftotext_path'] ?? 'NO CONFIGURADA') . "\n";
echo "Ruta python configurada:    " . ($config['python_path'] ?? 'NO CONFIGURADA') . "\n";
echo "Ruta poppler configurada:   " . ($config['poppler_path'] ?? 'NO CONFIGURADA') . "\n";
echo "</pre>";

// Verificar si las rutas existen
if (!empty($config['pdftotext_path']) && !file_exists($config['pdftotext_path'])) {
    echo "<div style='color:red; font-weight:bold;'>❌ ERROR CRÍTICO: La ruta de pdftotext NO EXISTE en este PC: {$config['pdftotext_path']}</div>";
} elseif (!empty($config['pdftotext_path'])) {
    echo "<div style='color:green;'>✅ pdftotext existe.</div>";
}

// 2. Buscar un PDF para probar
$pdfDir = __DIR__ . '/assets/expedientes/expedientes_subidos/tecnico-en-administracion';
$files = glob($pdfDir . '/*.pdf');
if (empty($files)) {
    die("❌ No se encontraron PDFs para probar en $pdfDir");
}
$pdfPath = $files[0];
echo "<h2>2. Archivo de Prueba</h2>";
echo "Usando: " . basename($pdfPath) . "<br>";

// 3. Probar Método 1: Spatie (pdftotext)
echo "<h2>3. Prueba Método 1: Spatie (pdftotext)</h2>";
try {
    // Simulación manual de lo que hace PDFProcessor
    if (file_exists($config['pdftotext_path'])) {
        $cmd = '"' . $config['pdftotext_path'] . '" -layout "' . $pdfPath . '" -';
        $output = shell_exec($cmd);
        echo "Ejecutando: $cmd <br>";
        echo "Resultado: " . (empty($output) ? "❌ VACÍO (Falló)" : "✅ OK (" . strlen($output) . " caracteres)") . "<br>";
    } else {
        echo "❌ SALTADO: Ejecutable pdftotext no encontrado<br>";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}

// 4. Probar Método 2: Smalot (PHP Puro)
echo "<h2>4. Prueba Método 2: Smalot (PHP Puro)</h2>";
try {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($pdfPath);
    $text = $pdf->getText();
    echo "Resultado: " . (empty($text) ? "❌ VACÍO (Es un PDF de imagen?)" : "✅ OK (" . strlen($text) . " caracteres)") . "<br>";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}

// 5. Probar Método 3: OCR Fallback (Tesseract)
echo "<h2>5. Prueba Método 3: OCR (Tesseract)</h2>";
$tesseractVersion = shell_exec('tesseract --version 2>&1');
if (empty($tesseractVersion) || strpos($tesseractVersion, 'not found') !== false) {
    echo "❌ Tesseract no está en el PATH del sistema.<br>";
} else {
    echo "✅ Tesseract detectado: <pre>$tesseractVersion</pre>";
    // Intentar OCR de la primera página
    echo "Intentando OCR de la primera página... (puede tardar)<br>";
    // (Simplificado)
}

echo "<h2>6. Conclusión</h2>";
echo "Si el Método 1 falló (Rojo) y el Método 2 falló (es imagen), y Tesseract no está, NO SE CAPTURARÁN DATOS.<br>";
echo "<strong>Solución:</strong> Ejecuta <code>ARREGLAR_RUTAS_OCR.bat</code> para corregir las rutas del Método 1.";
