<?php
require_once __DIR__ . '/lib/PDFProcessor.php';

$pdfPath = 'C:/laragon/www/EGRESAPP2/assets/expedientes/expedientes_subidos/NOELIA_ANDREA_DUBO_PIZARRO___000006_2.pdf';

echo "=== TESTING OCR ON: " . basename($pdfPath) . " ===\n\n";

try {
    $result = PDFProcessor::extractStructuredData($pdfPath);

    echo "SOURCE: " . ($result['source'] ?? 'unknown') . "\n";
    echo "COMMAND: " . ($result['command'] ?? 'N/A') . "\n\n";

    echo "=== EXTRACTED TEXT (first 1000 chars) ===\n";
    $text = $result['text'] ?? '';
    echo substr($text, 0, 1000) . "\n\n";

    echo "=== EXTRACTED FIELDS ===\n";
    echo json_encode($result['fields'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    // Guardar texto completo para análisis
    file_put_contents(__DIR__ . '/test_ocr_output.txt', $text);
    echo "Full text saved to: test_ocr_output.txt\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>