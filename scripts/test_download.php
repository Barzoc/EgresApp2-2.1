<?php

require_once __DIR__ . '/../lib/GoogleDriveClient.php';

$localDir = __DIR__ . '/../assets/expedientes/expedientes_subidos';
$driveClient = new GoogleDriveClient();

if (!$driveClient->isEnabled()) {
    die("Error: Google Drive no está habilitado.\n");
}

echo "Probando descarga...\n";
$remoteFiles = $driveClient->listFiles();
echo "Archivos remotos encontrados: " . count($remoteFiles) . "\n";

if (empty($remoteFiles)) {
    die("No hay archivos remotos para probar descarga.\n");
}

// Intentar descargar el primer archivo
$firstFile = array_key_first($remoteFiles);
$firstFileId = $remoteFiles[$firstFile];

echo "Intentando descargar: $firstFile ($firstFileId)\n";
$destination = $localDir . '/test_download_' . $firstFile;

if ($driveClient->downloadFile($firstFileId, $destination)) {
    echo "Descarga exitosa: $destination\n";
    // Limpiar
    unlink($destination);
} else {
    echo "Descarga fallida.\n";
}
