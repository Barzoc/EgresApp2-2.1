<?php
// scripts/verificar_sync.php
// Script para comparar inventario local vs Google Drive y detectar desajustes.

require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';

set_time_limit(0);

echo "Iniciando verificación de sincronización...\n\n";

// 1. Obtener Inventario Local
echo "1. Escaneando archivos locales...\n";
$baseDir = realpath(__DIR__ . '/../assets/expedientes');
$localFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
        // Normalizar separadores
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        $localFiles[basename($file->getFilename())] = $relativePath;
    }
}
echo "   -> Encontrados " . count($localFiles) . " archivos locales.\n";

// 2. Obtener Inventario Drive
echo "2. Escaneando archivos en Google Drive (esto puede tardar)...\n";
$driveClient = new GoogleDriveClient();

if (!$driveClient->isEnabled()) {
    die("Error: Google Drive no habilitado en configuración.\n");
}

$driveFiles = []; // [filename => ['id', 'parents']]
// Como listar todo recursivo es costoso, listamos por carpeta conocida en el Mapper
$mapping = DriveFolderMapper::getAll();
$mapperFolders = [];
foreach ($mapping as $m) {
    if (!empty($m['drive_folder_id'])) {
        $mapperFolders[$m['drive_folder_id']] = $m['label'];
    }
}
// También escanear root
$rootId = $driveClient->getRootFolderId();
$mapperFolders[$rootId] = 'ROOT';

$totalDrive = 0;

foreach ($mapperFolders as $folderId => $label) {
    echo "   -> Escaneando carpeta '$label' ($folderId)...\n";
    $files = $driveClient->listFolderFiles($folderId, false); // Solo archivos
    foreach ($files as $f) {
        $driveFiles[$f['name']] = [
            'id' => $f['id'],
            'folder_label' => $label,
            'folder_id' => $folderId
        ];
    }
    $totalDrive += count($files);
}
echo "   -> Encontrados $totalDrive archivos en carpetas monitoreadas de Drive.\n";


// 3. Comparar
echo "\n3. RESULTADOS DE COMPARACIÓN:\n";
echo "----------------------------------------------------------------\n";
printf("%-50s | %-30s | %-30s\n", "Archivo", "Ubicación Local", "Ubicación Drive");
echo "----------------------------------------------------------------\n";

$onlyLocal = 0;
$onlyDrive = 0;
$synced = 0;
$mismatch = 0;

// Unir listas
$allNames = array_unique(array_merge(array_keys($localFiles), array_keys($driveFiles)));
sort($allNames);

foreach ($allNames as $name) {
    $locPath = $localFiles[$name] ?? '--- NO EXISTE ---';
    $drvInfo = $driveFiles[$name] ?? null;
    $drvPath = $drvInfo ? $drvInfo['folder_label'] : '--- NO EXISTE ---';

    // Determinar estado
    if ($locPath !== '--- NO EXISTE ---' && $drvInfo) {
        // Verificar si la ubicación "semántica" coincide
        // Local: tecnico-en-administracion/file.pdf
        // Drive: TECNICO EN ADMINISTRACION

        // Extraer carpeta local
        $localDir = dirname($locPath);
        if ($localDir === '.')
            $localDir = 'ROOT';

        // Este check es visual, no estricto, ya que los nombres de carpeta difieren
        $status = "OK";
        $synced++;
    } elseif ($locPath !== '--- NO EXISTE ---') {
        $status = "SOLO LOCAL";
        $onlyLocal++;
    } elseif ($drvInfo) {
        $status = "SOLO DRIVE";
        $onlyDrive++;
    }

    // Imprimir solo diferencias o resumen
    if ($status !== "OK") {
        printf("%-50.50s | %-30.30s | %-30.30s | %s\n", $name, $localDir ?? $locPath, $drvPath, $status);
    }
}

echo "----------------------------------------------------------------\n";
echo "Resumen:\n";
echo "Total Archivos Únicos: " . count($allNames) . "\n";
echo "Sincronizados (En ambos lados): $synced\n";
echo "Solo en Local: $onlyLocal\n";
echo "Solo en Drive: $onlyDrive\n";

if ($onlyLocal > 0) {
    echo "\nATENCIÓN: Hay $onlyLocal archivos que están en local pero NO en las carpetas de Drive monitoreadas.\n";
    echo "Posible causa: No se han subido o están en una carpeta de Drive que no está en el mapa.\n";
}

if ($onlyDrive > 0) {
    echo "\nNOTA: Hay $onlyDrive archivos en Drive que no están en local. Podrían ser respaldos antiguos.\n";
}
