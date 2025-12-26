<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

// Buscar PDF
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
    die('No se encontró PDF');
}

echo '<h1>Test N&uacute;mero Manuscrito</h1>';
echo '<p><strong>Archivo:</strong> ' . basename($pdfPath) . '</p>';

// Usar método existente del PDFProcessor
echo '<h2>Extrayendo datos estructurados...</h2>';
$data = PDFProcessor::extractStructuredData($pdfPath);

echo '<h3>Campos extraídos:</h3>';
echo '<pre>';
print_r($data['fields']);
echo '</pre>';

echo '<h3>Configuración:</h3>';
echo '<ul>';
echo '<li>M&eacute;todo: ' . $data['source'] . '</li>';
echo '<li>Longitud texto: ' . strlen($data['text'] ?? '') . ' caracteres</li>';
echo '</ul>';

// Información sobre número de certificado
if (empty($data['fields']['numero_certificado'])) {
    echo '<div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0;">';
    echo '<h3>ℹ N&uacute;mero de certificado no detectado</h3>';
    echo '<p>El sistema busca n&uacute;meros manuscritos en la esquina superior derecha.</p>';
    echo '<p>Si skip_slow_extraction=false en config, el sistema intentar&aacute; OCR de im&aacute;genes.</p>';
    echo '</div>';
}

// Mostrar configuración
$config = require __DIR__ . '/config/pdf.php';
echo '<h3>Configuración actual:</h3>';
echo '<pre>';
print_r($config);
echo '</pre>';
?>
