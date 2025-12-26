<?php
// scripts/fix_drive_root.php

require_once __DIR__ . '/../lib/PDFProcessor.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';
require_once __DIR__ . '/../lib/GoogleDriveClient.php';

// Disable timeout
set_time_limit(0);
ini_set('memory_limit', '1G');

$client = new GoogleDriveClient();
if (!$client->isEnabled()) {
    die("Google Drive no habilitado.\n");
}

$rootId = $client->getRootFolderId();
echo "Root Folder ID: $rootId\n";

// Get all files in Root
echo "Listando archivos en raíz...\n";
$files = $client->listFolderFiles($rootId); // This returns array of ['id', 'name', 'mimeType', ...]
echo "Encontrados " . count($files) . " items.\n";

$localBase = realpath(__DIR__ . '/../assets/expedientes/expedientes_subidos');

$moved = 0;
$skipped = 0;
$errors = 0;

foreach ($files as $file) {
    if ($file['isFolder']) {
        continue;
    }

    $name = $file['name'];
    $fileId = $file['id'];

    echo "Procesando: $name ... ";

    // 1. Try to find local match
    // Handle duplicates like "Name_1.pdf" -> "Name.pdf"
    $cleanName = preg_replace('/_\d+(\.pdf)$/i', '$1', $name);
    $localPath = $localBase . DIRECTORY_SEPARATOR . $cleanName;

    // Direct match check
    if (!file_exists($localPath)) {
        // Fallback: Check if the name IS the clean name but maybe the file on disk is different?
        // Assume file matches for now.
        // If not found, download to temp?

        $tempPath = sys_get_temp_dir() . '/drive_fix_' . $fileId . '.pdf';
        echo "[Descargando para inspección] ";
        if ($client->downloadFile($fileId, $tempPath)) {
            $localPath = $tempPath;
        } else {
            echo "SKIP (No local y falló descarga)\n";
            $skipped++;
            continue;
        }
    }

    try {
        // 2. Extract Title
        // Use cached extraction if possible? No, we need to be sure.
        // But for speed, if we trust local file is same as remote, we parse local.

        $data = PDFProcessor::extractStructuredData($localPath);
        $fields = $data['fields'] ?? [];
        $title = $fields['titulo'] ?? $fields['titulo_obtenido'] ?? $fields['especialidad'] ?? null;

        // Cleanup temp
        if (strpos($localPath, 'drive_fix_') !== false) {
            @unlink($localPath);
        }

        if (!$title) {
            echo "SKIP (Sin titulo extraido)\n";
            $skipped++;
            continue;
        }

        // 3. Resolve Target
        $mapping = DriveFolderMapper::resolveByTitle($title);
        $targetId = $mapping['drive_folder_id'] ?? null;
        $targetLabel = $mapping['label'] ?? 'Unknown';

        if ($targetId && $targetId !== $rootId) {
            // 4. Move
            if ($client->moveFileToFolder($fileId, $targetId)) {
                echo "MOVED -> $targetLabel ($targetId)\n";
                $moved++;
            } else {
                echo "ERROR (Move failed)\n";
                $errors++;
            }
        } else {
            echo "SKIP (Ya en destino o sin mapping: $targetLabel)\n";
            $skipped++;
        }

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\nResumen:\n";
echo "Movidos: $moved\n";
echo "Omitidos: $skipped\n";
echo "Errores: $errors\n";
