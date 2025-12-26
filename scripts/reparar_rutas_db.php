<?php
// scripts/reparar_rutas_db.php
// Script para actualizar las rutas en la base de datos coincidiendo con la ubicación física real.

require_once __DIR__ . '/../modelo/Conexion.php';

set_time_limit(0);

echo "Iniciando reparación de rutas en BD...\n\n";

// 1. Mapa del Sistema de Archivos
echo "1. Escaneando sistema de archivos (assets/expedientes)...\n";
$baseDir = realpath(__DIR__ . '/../assets/expedientes');
$fileMap = []; // [basename => relative_path]

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath); // Normalize to forward slashes for consistency
        $fileMap[basename($file->getFilename())] = $relativePath;
    }
}
echo "   -> Encontrados " . count($fileMap) . " archivos físicos.\n";

// 2. Procesar Base de Datos
echo "2. Revisando base de datos...\n";
$db = new Conexion();
$sql = "SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado WHERE expediente_pdf IS NOT NULL AND expediente_pdf != ''";
$stmt = $db->pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$notFound = 0;
$alreadyCorrect = 0;

foreach ($rows as $row) {
    $currentDbValue = $row['expediente_pdf'];
    $basename = basename($currentDbValue);

    // Buscar en el mapa físico
    if (isset($fileMap[$basename])) {
        $realRelativePath = $fileMap[$basename];

        // Comparar valor actual DB vs Realidad
        // Normalizamos separators en DB Value tambien por si acaso
        $currentNormalized = str_replace('\\', '/', $currentDbValue);

        if ($currentNormalized !== $realRelativePath) {
            // Actualizar DB
            $updateSql = "UPDATE egresado SET expediente_pdf = :newPath WHERE identificacion = :id";
            $updateStmt = $db->pdo->prepare($updateSql);
            $updateStmt->execute([
                ':newPath' => $realRelativePath,
                ':id' => $row['identificacion']
            ]);
            echo "UPDATE [{$row['identificacion']}]: '$currentDbValue' -> '$realRelativePath'\n";
            $updated++;
        } else {
            $alreadyCorrect++;
        }
    } else {
        echo "WARNING [{$row['identificacion']}]: Archivo físico no encontrado para '{$currentDbValue}'\n";
        $notFound++;
    }
}

echo "\n------------------------------------------------\n";
echo "Resumen:\n";
echo "Total revisados: " . count($rows) . "\n";
echo "Actualizados: $updated\n";
echo "Ya correctos: $alreadyCorrect\n";
echo "Físico no encontrado: $notFound\n";
