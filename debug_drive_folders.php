<?php
require_once __DIR__ . '/lib/GoogleDriveClient.php';
require_once __DIR__ . '/lib/DriveFolderMapper.php';

$client = new GoogleDriveClient();
$folders = require __DIR__ . '/config/drive_folders.php';

echo "=== Verificando acceso a carpetas de Drive ===\n";

foreach ($folders as $folder) {
    if (empty($folder['drive_folder_id'])) continue;
    
    $id = $folder['drive_folder_id'];
    $alias = $folder['aliases'][0];
    
    echo "Verificando ID: $id ($alias)... ";
    
    try {
        $meta = $client->getFileMetadata($id);
        if ($meta) {
            echo "[OK] Nombre: " . $meta['name'] . "\n";
            // Check parents
            if (!empty($meta['parents'])) {
                echo "    -> Padre: " . implode(', ', $meta['parents']) . "\n";
            }
        } else {
            echo "[ERROR] No encontrado o sin acceso.\n";
        }
    } catch (Throwable $e) {
        echo "[EXCEPCION] " . $e->getMessage() . "\n";
    }
}
