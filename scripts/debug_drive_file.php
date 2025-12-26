<?php
// scripts/debug_drive_file.php
require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';

$filename = "Claudio_Alexis_Ardiles_Fern__ndez.pdf";
echo "Investigando archivo: $filename\n\n";

// 1. Check Local
$baseDir = realpath(__DIR__ . '/../assets/expedientes');
$foundLocal = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getFilename() === $filename) {
        $foundLocal[] = $file->getPathname();
    }
}

if (empty($foundLocal)) {
    echo "LOCAL: No encontrado en assets/expedientes.\n";
} else {
    echo "LOCAL: Encontrado en:\n";
    foreach ($foundLocal as $path) {
        echo "  - $path\n";
    }
}

// 2. Check Drive
echo "\nDRIVE: Buscando en Google Drive...\n";
$client = new GoogleDriveClient();
if (!$client->isEnabled()) {
    die("Drive no habilitado.\n");
}

// Search by exact name
$foundExact = $client->findFileByName($filename);
if ($foundExact) {
    echo "  [Exact Match] Encontrado: ID={$foundExact['id']}, Name={$foundExact['name']}\n";
} else {
    echo "  [Exact Match] No encontrado.\n";
}

// Search by partial name (replace underscores with spaces or remove special chars)
$cleanName = str_replace(['_', '.pdf'], ' ', $filename);
$queryName = 'Ardiles'; // Search by surname to be sure
echo "  Buscando por apellido ('$queryName')...\n";

$service = new ReflectionClass($client);
$property = $service->getProperty('service');
$property->setAccessible(true);
$driveService = $property->getValue($client);

try {
    $optParams = [
        'q' => "name contains '$queryName' and trashed = false",
        'fields' => 'files(id, name, parents)',
        'supportsAllDrives' => true,
        'includeItemsFromAllDrives' => true,
    ];
    $results = $driveService->files->listFiles($optParams);

    if (count($results->getFiles()) == 0) {
        echo "  [Partial] No se encontraron coincidencias.\n";
    } else {
        echo "  [Partial] Coincidencias encontradas:\n";
        foreach ($results->getFiles() as $file) {
            echo "    - Name: " . $file->getName() . " (ID: " . $file->getId() . ")\n";
            // Check parents
            if ($file->getParents()) {
                foreach ($file->getParents() as $parentId) {
                    $parentMeta = $client->getFileMetadata($parentId);
                    echo "      Parent: " . ($parentMeta['name'] ?? $parentId) . "\n";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "Error buscar partial: " . $e->getMessage() . "\n";
}

