<?php
// scripts/auto_organizar.php
// Script para organizar automáticamente los expedientes en sus carpetas correspondientes.
// Uso ideal: Programar con Tareas de Windows para ejecutar cada 5-15 minutos.

require_once __DIR__ . '/../lib/PDFProcessor.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';
require_once __DIR__ . '/../services/ExpedienteProcessor.php';

// Configuración
ini_set('memory_limit', '512M');
set_time_limit(600); // 10 minutos máx

$baseDir = realpath(__DIR__ . '/../assets/expedientes');
$inboxDir = $baseDir . '/expedientes_subidos';
$logFile = __DIR__ . '/../logs/auto_organizar.log';

if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logger($msg)
{
    global $logFile;
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    echo $msg . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Bloqueo simple para evitar ejecuciones concurrentes
$lockFile = sys_get_temp_dir() . '/egresapp_organizer.lock';
if (file_exists($lockFile)) {
    if (time() - filemtime($lockFile) > 1200) { // Lock de 20 min max
        unlink($lockFile);
    } else {
        die("El script ya está en ejecución (Lock file exists).\n");
    }
}
touch($lockFile);

try {
    logger("Iniciando auto-organización...");

    if (!is_dir($inboxDir)) {
        throw new Exception("Directorio de entrada no existe: $inboxDir");
    }

    $files = glob($inboxDir . '/*.pdf');
    if (empty($files)) {
        logger("No hay archivos pendientes en expedientes_subidos.");
    } else {
        logger("Encontrados " . count($files) . " archivos pendientes.");

        $processor = new ExpedienteProcessor();
        $reflector = new ReflectionClass('ExpedienteProcessor');
        $method = $reflector->getMethod('relocateExpedienteAssets');
        $method->setAccessible(true);

        $moved = 0;
        $skipped = 0;

        foreach ($files as $filepath) {
            $filename = basename($filepath);
            try {
                // Extraer datos
                $data = PDFProcessor::extractStructuredData($filepath);
                $fields = $data['fields'] ?? [];
                $title = $fields['titulo'] ?? $fields['titulo_obtenido'] ?? $fields['especialidad'] ?? null;

                if (empty($title)) {
                    // Intento fallido
                    $skipped++;
                    continue;
                }

                // Verificar destino
                $mapping = DriveFolderMapper::resolveByTitle($title);
                $targetFolder = $mapping['local_folder'] ?? '';

                if (!empty($targetFolder)) {
                    // Mover
                    $job = [
                        'id' => 0,
                        'filename' => $filename,
                        'filepath' => $filepath,
                        'id_expediente' => null
                    ];
                    $driveInfo = ['drive_id' => null, 'drive_link' => null];

                    $method->invokeArgs($processor, [&$job, $fields, &$driveInfo]);

                    if (!file_exists($filepath) && $job['filepath'] !== $filepath) {
                        logger("MOVED: $filename -> $targetFolder");
                        $moved++;
                    }
                } else {
                    $skipped++;
                }

            } catch (Exception $e) {
                logger("ERROR procesando $filename: " . $e->getMessage());
            }
        }

        logger("Resumen: Movidos $moved, Omitidos/Fallidos $skipped");
    }

} catch (Exception $e) {
    logger("CRITICAL ERROR: " . $e->getMessage());
} finally {
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}
