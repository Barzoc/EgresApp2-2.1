<?php
require_once __DIR__ . '/lib/GoogleDriveClient.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

echo "=== VERIFICACIÓN DE CORRECCIÓN OCR ===\n\n";

try {
    $drive = new GoogleDriveClient();
    // ID de la carpeta TECNICO EN ADMINISTRACION
    $folderId = '1P5yrh__kb7KoJOSV8jdNF2445YJ2hF8p';

    echo "📂 Listando archivos...\n";
    $files = $drive->listFolderFiles($folderId, false);

    foreach ($files as $f) {
        // Buscamos el archivo específico que dio problemas
        if (stripos($f['name'], 'LUIS_FABIAN') !== false) {
            echo "📄 Procesando: {$f['name']}\n";
            echo "   ID: {$f['id']}\n\n";

            $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'verify_fix_' . uniqid() . '.pdf';

            echo "⬇️  Descargando...\n";
            if ($drive->downloadFile($f['id'], $temp)) {
                echo "🔍 Extrayendo datos...\n";
                $result = PDFProcessor::extractStructuredData($temp);

                echo "\n=== RESULTADOS ===\n";
                echo json_encode($result['fields'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // Verificar específicamente el año
                $anio = $result['fields']['anio_egreso'] ?? 'NULL';
                echo "\n\n🎯 AÑO EGRESO DETECTADO: $anio\n";

                if ($anio === '2009') {
                    echo "✅ ÉXITO: El año se detectó correctamente.\n";
                } else {
                    echo "❌ FALLO: El año no se detectó o es incorrecto.\n";
                }

                unlink($temp);
            } else {
                echo "❌ Error al descargar archivo.\n";
            }
            break;
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
