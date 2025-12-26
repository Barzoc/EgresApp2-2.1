<?php
// scripts/validar_drive_links.php
// Script para validar si los links de Drive en la DB son válidos y corregirlos si apuntan a archivos borrados.

require_once __DIR__ . '/../modelo/Conexion.php';
require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';

set_time_limit(0);

echo "Iniciando validación de enlaces de Drive...\n\n";

$db = new Conexion();
$driveClient = new GoogleDriveClient();

if (!$driveClient->isEnabled()) {
    die("Error: Google Drive no habilitado.\n");
}

// Obtener egresados con ID de Drive
$sql = "SELECT identificacion, nombreCompleto, expediente_pdf, expediente_drive_id, tituloObtenido FROM egresado WHERE expediente_drive_id IS NOT NULL AND expediente_drive_id != ''";
$stmt = $db->pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($rows);
echo "Procesando $total registros...\n";

$updated = 0;
$issues = 0;
$ok = 0;

foreach ($rows as $row) {
    $id = $row['identificacion'];
    $driveId = $row['expediente_drive_id'];
    $nombre = $row['nombreCompleto'];
    $localFile = basename($row['expediente_pdf'] ?? '');

    echo "ID [$id] $nombre... ";

    // 1. Verificar metadata del archivo actual
    $meta = $driveClient->getFileMetadata($driveId);

    $isValid = false;
    if ($meta && empty($meta['trashed'])) {
        $isValid = true;
        // echo "OK (ID: $driveId)\n";
        $ok++;
    } else {
        echo "INVALIDO/PAPELERA (ID: $driveId). Buscando reemplazo...\n";
        $issues++;

        // 2. Buscar archivo correcto
        // Intentar por nombre exacto del PDF local
        $found = null;
        if ($localFile) {
            $found = $driveClient->findFileByName($localFile);
        }

        // Si no, intentar por nombre + apellido
        if (!$found) {
            // Fuzzy search logic similar to mapper could go here, but simple name search is safer first
            // Clean name logic
            // ...
        }

        if ($found) {
            $newId = $found['id'];
            $newLink = $found['webViewLink'] ?? $found['webContentLink'];

            // Actualizar DB
            $upSql = "UPDATE egresado SET expediente_drive_id = :did, expediente_drive_link = :dlink WHERE identificacion = :id";
            $upStmt = $db->pdo->prepare($upSql);
            $upStmt->execute([
                ':did' => $newId,
                ':dlink' => $newLink,
                ':id' => $id
            ]);

            echo "   -> ACTUALIZADO a ID: $newId\n";
            $updated++;
        } else {
            echo "   -> ERROR: No se encontró reemplazo en Drive.\n";
            // Podríamos borrar el ID inválido para limpiar?
            // "DELETE" logic?
            // $db->pdo->query("UPDATE egresado SET expediente_drive_id = NULL, expediente_drive_link = NULL WHERE identificacion = $id");
            // echo "   -> Limpiada referencia rota.\n";
        }
    }

    if ($isValid)
        echo "OK\n";
}

echo "\n------------------------------------------------\n";
echo "Resumen:\n";
echo "Total: $total\n";
echo "Válidos: $ok\n";
echo "Corregidos: $updated\n";
echo "Sin solución: " . ($issues - $updated) . "\n";
