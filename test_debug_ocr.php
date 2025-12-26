<?php

require_once __DIR__ . '/lib/GoogleDriveClient.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

echo "=== DEBUG: Análisis de extracción OCR ===\n\n";

try {
    $driveClient = new GoogleDriveClient();
    $folderId = '1P5yrh__kb7KoJOSV8jdNF2445YJ2hF8p';

    // Buscar el archivo específico
    $files = $driveClient->listFolderFiles($folderId, false);
    $targetFile = null;

    foreach ($files as $file) {
        if (stripos($file['name'], 'LUIS_FABIAN_MALLA') !== false) {
            $targetFile = $file;
            break;
        }
    }

    if (!$targetFile) {
        throw new Exception('Archivo no encontrado');
    }

    echo "Archivo: {$targetFile['name']}\n\n";

    // Descargar
    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'debug_' . uniqid() . '.pdf';
    $driveClient->downloadFile($targetFile['id'], $tempPath);

    echo "⏳ Extrayendo texto con OCR...\n\n";

    // Extraer texto crudo
    $rawText = PDFProcessor::extractTextFromPDF($tempPath);

    // Guardar texto crudo para inspección
    $debugTextFile = __DIR__ . '/assets/expedientes/debug_texto_crudo.txt';
    file_put_contents($debugTextFile, $rawText);

    echo "=== TEXTO COMPLETO EXTRAÍDO ===\n";
    echo $rawText;
    echo "\n\n=== FIN TEXTO ===\n\n";

    // Buscar patrones de año
    echo "=== BÚSQUEDA DE PATRONES ===\n\n";

    if (preg_match_all('/AÑO|ANO|EGRESO|2010|2011|2012|2013|2014|2015/iu', $rawText, $matches, PREG_OFFSET_CAPTURE)) {
        echo "Coincidencias encontradas:\n";
        foreach ($matches[0] as $match) {
            $text = $match[0];
            $pos = $match[1];
            $context = substr($rawText, max(0, $pos - 30), 100);
            echo "  - '$text' en posición $pos\n";
            echo "    Contexto: " . trim($context) . "\n\n";
        }
    }

    // Extraer datos estructurados
    echo "=== DATOS PARSEADOS ===\n";
    $result = PDFProcessor::extractStructuredData($tempPath);
    echo json_encode($result['fields'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Limpiar
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }

    echo "\n\n✅ Debug guardado en: $debugTextFile\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
