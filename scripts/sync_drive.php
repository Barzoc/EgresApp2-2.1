<?php

require_once __DIR__ . '/../lib/GoogleDriveClient.php';

// Configuración
$localDir = __DIR__ . '/../assets/expedientes/expedientes_subidos';
$driveClient = new GoogleDriveClient();

if (!$driveClient->isEnabled()) {
    die("Error: Google Drive no está habilitado o configurado correctamente.\n");
}

echo "Iniciando sincronización...\n";
echo "Directorio local: $localDir\n";

// Asegurar que el directorio local existe
if (!is_dir($localDir)) {
    mkdir($localDir, 0755, true);
}

// 1. Obtener lista de archivos remotos
echo "Obteniendo lista de archivos en Google Drive...\n";
$remoteFiles = $driveClient->listFiles();
echo "Encontrados " . count($remoteFiles) . " archivos en Drive.\n";

// 2. Obtener lista de archivos locales
$localFiles = array_diff(scandir($localDir), ['.', '..']);
$localFilesMap = array_flip($localFiles); // filename => index (solo para búsqueda rápida)
echo "Encontrados " . count($localFiles) . " archivos locales.\n";

// 3. Sincronizar Local -> Drive (Subir faltantes)
echo "\n--- Sincronizando Local -> Drive ---\n";
foreach ($localFiles as $filename) {
    if (!isset($remoteFiles[$filename])) {
        echo "Subiendo: $filename... ";
        $filePath = $localDir . '/' . $filename;
        try {
            $result = $driveClient->uploadFile($filePath, $filename);
            echo "OK (ID: " . $result['id'] . ")\n";
            // Actualizar lista remota para evitar re-descarga inmediata si corremos lógica cruzada
            $remoteFiles[$filename] = $result['id']; 
        } catch (Throwable $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        // echo "Ya existe en Drive: $filename\n";
    }
}

// 4. Sincronizar Drive -> Local (Descargar faltantes)
echo "\n--- Sincronizando Drive -> Local ---\n";
foreach ($remoteFiles as $filename => $fileId) {
    if (!isset($localFilesMap[$filename])) {
        echo "Descargando: $filename... ";
        $destinationPath = $localDir . '/' . $filename;
        try {
            if ($driveClient->downloadFile($fileId, $destinationPath)) {
                echo "OK\n";
            } else {
                echo "FALLÓ\n";
            }
        } catch (Throwable $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        // echo "Ya existe localmente: $filename\n";
    }
}

echo "\nSincronización completada.\n";
