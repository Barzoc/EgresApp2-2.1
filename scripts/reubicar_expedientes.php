<?php
// scripts/reubicar_expedientes.php

require_once __DIR__ . '/../lib/PDFProcessor.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';
require_once __DIR__ . '/../services/ExpedienteProcessor.php';

// disable timeout
set_time_limit(0);

$baseDir = realpath(__DIR__ . '/../assets/expedientes');
$sourceDir = $baseDir . '/expedientes_subidos';

echo "Iniciando reubicación de expedientes...\n";
echo "Carpeta origen: $sourceDir\n";
echo "---------------------------------------------------\n";

if (!is_dir($sourceDir)) {
    die("Error: No existe el directorio de expedientes subidos.\n");
}

$files = glob($sourceDir . '/*.pdf');
$total = count($files);
$moved = 0;
$skipped = 0;
$errors = 0;

echo "Encontrados $total archivos PDF para procesar.\n\n";

// Helper helper to access the private relocate method
$processor = new ExpedienteProcessor();
$reflector = new ReflectionClass('ExpedienteProcessor');
$method = $reflector->getMethod('relocateExpedienteAssets');
$method->setAccessible(true);

foreach ($files as $index => $filepath) {
    $filename = basename($filepath);
    $counter = $index + 1;
    echo "[$counter/$total] Procesando: $filename ... ";

    try {
        // 1. Extraer titulo con OCR (fast-path + AI fallback handled by PDFProcessor)
        $data = PDFProcessor::extractStructuredData($filepath);
        $fields = $data['fields'] ?? [];
        $title = $fields['titulo'] ?? $fields['titulo_obtenido'] ?? $fields['especialidad'] ?? null;

        if (empty($title)) {
            echo "SKIP (No se detectó título) - Filename: $filename\n";
            $skipped++;
            continue;
        }

        // 2. Resolver carpeta destino
        $mapping = DriveFolderMapper::resolveByTitle($title);
        $targetFolder = $mapping['local_folder'] ?? '';

        if (empty($targetFolder)) {
            echo "SKIP (Título '$title' no mapa a carpeta) - Filename: $filename\n";
            $skipped++;
            continue;
        }

        // 3. Ejecutar reubicación usando ExpedienteProcessor logic
        $job = [
            'id' => 0,
            'filename' => $filename,
            'filepath' => $filepath,
            'id_expediente' => null
        ];
        $driveInfo = ['drive_id' => null, 'drive_link' => null];

        $method->invokeArgs($processor, [&$job, $fields, &$driveInfo]);

        if ($job['filepath'] !== $filepath && !file_exists($filepath)) {
            echo "MOVED -> {$mapping['local_folder']}\n";
            $moved++;
        } else {
            if (dirname($job['filepath']) === dirname($filepath)) {
                echo "NO-OP (Ya está en lugar o fallo movimiento)\n";
            } else {
                echo "DONE\n";
                $moved++;
            }
        }

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n---------------------------------------------------\n";
echo "Resumen Final:\n";
echo "Total: $total\n";
echo "Movidos: $moved\n";
echo "Omitidos: $skipped\n";
echo "Errores: $errors\n";
