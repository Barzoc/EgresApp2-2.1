<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

$pdfPath = 'C:/Users/xerox/Desktop/EXPEDIENTES/LISTO PARA SUBIR/EDUARDO ANDRÉS GUERRERO TORRES.pdf';

echo "===========================================\n";
echo "TEST DE EXTRACCIÓN DE TEXTO\n";
echo "===========================================\n\n";

echo "[1] Probando extractTextFromPDF()...\n";
$text = PDFProcessor::extractTextFromPDF($pdfPath);
echo "    Longitud texto: " . strlen($text) . " caracteres\n";

if (strlen($text) > 0) {
    echo "\n[2] Muestra del texto extraído:\n";
    echo "-------------------------------------------\n";
    echo substr($text, 0, 800) . "\n";
    echo "-------------------------------------------\n\n";
} else {
    echo "    ✗ NO SE EXTRAJO TEXTO\n\n";
}

echo "[3] Probando parseCertificateData()...\n";
if (strlen($text) > 0) {
    $parsed = PDFProcessor::parseCertificateData($text, $pdfPath);
    echo "    Campos encontrados:\n";
    foreach ($parsed as $key => $value) {
        echo sprintf("      %-20s: %s\n", $key, $value ?: '(vacío)');
    }
} else {
    echo "    ✗ No se puede parsear - no hay texto\n";
}

echo "\n[4] Probando extractStructuredData()...\n";
$structured = PDFProcessor::extractStructuredData($pdfPath);
echo "    Source: " . ($structured['source'] ?? 'unknown') . "\n";
echo "    Campos extraídos:\n";
foreach (($structured['fields'] ?? []) as $key => $value) {
    echo sprintf("      %-20s: %s\n", $key, $value ?: '(vacío)');
}
