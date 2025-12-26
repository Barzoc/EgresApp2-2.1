<?php

require_once __DIR__ . '/../modelo/Egresado.php';
require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveSync.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    session_start();
} catch (Throwable $e) {
    // Ignorar si las cabeceras ya fueron enviadas
}

$accion = $_POST['accion'] ?? '';
$egresado = new Egresado();

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function getExpedienteDirectory(): string
{
    $dir = realpath(__DIR__ . '/../assets/expedientes');
    if ($dir === false) {
        $dir = __DIR__ . '/../assets/expedientes';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    return rtrim($dir, DIRECTORY_SEPARATOR);
}

function buildLocalUrl(?string $fileName): ?string
{
    if (empty($fileName)) {
        return null;
    }
    $normalized = str_replace('\\', '/', ltrim($fileName, '/\\'));
    $segments = array_filter(explode('/', $normalized), 'strlen');
    $encodedPath = implode('/', array_map('rawurlencode', $segments));
    return '../assets/expedientes/' . $encodedPath;
}

function slugifyName(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '';
    }

    // Manual mapping for common Spanish accents to ensure consistency
    $map = [
        'Á' => 'A',
        'É' => 'E',
        'Í' => 'I',
        'Ó' => 'O',
        'Ú' => 'U',
        'Ü' => 'U',
        'Ñ' => 'N',
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n'
    ];

    $name = strtr($name, $map);
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
    if ($transliterated === false) {
        $transliterated = $name;
    }

    $upper = strtoupper($transliterated);
    $slug = preg_replace('/[^A-Z0-9]+/i', '_', $upper);
    $slug = preg_replace('/_+/', '_', $slug ?? '');
    return trim($slug, '_');
}

function findLocalFileByName(string $name, string $dir): ?string
{
    $name = trim($name);
    if ($name === '') {
        return null;
    }

    // Create a fuzzy regex pattern from the name
    // 1. Normalize accents
    $map = [
        'Á' => 'A',
        'É' => 'E',
        'Í' => 'I',
        'Ó' => 'O',
        'Ú' => 'U',
        'Ü' => 'U',
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        // Treat Ñ as a wildcard separator because it's often handled inconsistently
        'Ñ' => '.*',
        'ñ' => '.*'
    ];
    $cleanName = strtr($name, $map);

    // 2. Keep only alphanumeric and wildcards
    $cleanName = preg_replace('/[^A-Za-z0-9\.\*]+/', '.*', $cleanName);

    // 3. Build regex: Start with anything, match the name parts, end with .pdf
    $regex = '/.*' . $cleanName . '.*\.pdf$/i';

    try {
        $directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $info) {
            if ($info->isFile()) {
                // Check if filename matches the fuzzy pattern
                if (preg_match($regex, $info->getFilename())) {
                    // Return relative path
                    $fullPath = str_replace('\\', '/', $info->getPathname());
                    $dirNormalized = str_replace('\\', '/', $dir);

                    if (strpos($fullPath, $dirNormalized) === 0) {
                        return ltrim(substr($fullPath, strlen($dirNormalized)), '/');
                    }
                    return $info->getFilename();
                }
            }
        }
    } catch (Throwable $e) {
        // Ignore errors during search
    }

    return null;
}

if (empty($accion)) {
    respond(['success' => false, 'mensaje' => 'Acción no especificada'], 400);
}

$rawId = $_POST['id'] ?? '';
$id = is_string($rawId) ? trim($rawId) : trim((string) $rawId);
if ($id === '') {
    respond(['success' => false, 'mensaje' => 'Identificador inválido'], 400);
}

$idSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $id);
if ($idSlug === '') {
    $idSlug = 'expediente';
}

$storage = $egresado->ObtenerExpedienteStorage($id);
if (!$storage) {
    respond(['success' => false, 'mensaje' => 'Expediente no encontrado'], 404);
}

$dir = getExpedienteDirectory();
$localFileName = $storage['expediente_pdf'] ?? null;
$localPath = $localFileName ? $dir . DIRECTORY_SEPARATOR . $localFileName : null;
$localExists = $localPath && is_file($localPath);
$localUrl = buildLocalUrl($localFileName);

if (!$localExists) {
    // 1. Try to find the file by its name recursively in the directory
    if ($localFileName) {
        $foundPath = findLocalFileByName($localFileName, $dir);
        if ($foundPath) {
            $localFileName = $foundPath;
            $localPath = $dir . DIRECTORY_SEPARATOR . $localFileName;
            $localExists = is_file($localPath);
            $localUrl = buildLocalUrl($localFileName);

            // Update DB with the correct relative path if found
            if ($localExists && !empty($storage['identificacion'])) {
                $egresado->ActualizarExpedienteStorage($storage['identificacion'], ['archivo' => $localFileName]);
            }
        }
    }

    // 2. Fallback: Try to find by user name if filename search failed
    if (!$localExists) {
        $fallbackName = findLocalFileByName($storage['nombreCompleto'] ?? '', $dir);
        if ($fallbackName) {
            $localFileName = $fallbackName;
            $localPath = $dir . DIRECTORY_SEPARATOR . $localFileName;
            $localExists = is_file($localPath);
            $localUrl = buildLocalUrl($localFileName);
            if ($localExists && !empty($storage['identificacion'])) {
                $egresado->ActualizarExpedienteStorage($storage['identificacion'], ['archivo' => $localFileName]);
            }
        }
    }
}
$driveWarnings = [];
$driveExists = false;
$driveLink = $storage['expediente_drive_link'] ?? null;
$driveId = $storage['expediente_drive_id'] ?? null;

switch ($accion) {
    case 'verificar':
        // Intentar verificar por drive_id primero
        if (!empty($driveId)) {
            try {
                $driveClient = new GoogleDriveClient();
                if ($driveClient->isEnabled()) {
                    $metadata = $driveClient->getFileMetadata($driveId);
                    if ($metadata) {
                        $driveExists = true;
                        if (empty($driveLink)) {
                            $driveLink = $metadata['webViewLink'] ?? $metadata['webContentLink'] ?? null;
                        }
                    } else {
                        // Drive ID no válido (archivo fue borrado), intentar buscar por nombre
                        $driveWarnings[] = 'El archivo con ID almacenado no existe. Buscando alternativas...';
                    }
                }
            } catch (Throwable $e) {
                $driveWarnings[] = 'No se pudo verificar en Drive: ' . $e->getMessage();
            }
        }

        // Si no se verificó por ID (vacío o falló), buscar por nombre de archivo en Drive
        if (!$driveExists && $localFileName) {
            try {
                $driveClient = $driveClient ?? new GoogleDriveClient();
                if ($driveClient->isEnabled()) {
                    // Usar findFileByName que busca globalmente
                    $foundFile = $driveClient->findFileByName($localFileName);

                    if ($foundFile) {
                        $driveExists = true;
                        $driveId = $foundFile['id'];
                        $driveLink = $foundFile['webViewLink'] ?? $foundFile['webContentLink'] ?? null;

                        $driveWarnings[] = "✓ Encontrado en Drive: " . ($foundFile['name'] ?? 'Archivo');

                        // Actualizar BD con el ID encontrado
                        if (!empty($storage['identificacion'])) {
                            $egresado->ActualizarExpedienteStorage($storage['identificacion'], [
                                'drive_id' => $driveId,
                                'drive_link' => $driveLink
                            ]);
                        }
                    } else {
                        $driveWarnings[] = "No se encontró el archivo '$localFileName' en Google Drive.";
                    }
                }
            } catch (Throwable $e) {
                $driveWarnings[] = 'Error al buscar en Drive: ' . $e->getMessage();
            }
        }

        respond([
            'success' => true,
            'local_exists' => (bool) $localExists,
            'drive_exists' => $driveExists,
            'local_url' => $localExists ? $localUrl : null,
            'drive_url' => $driveExists ? ($driveLink ?? null) : null,
            'drive_id' => $driveId,
            'warnings' => $driveWarnings,
        ]);
        break;

    case 'restaurar_local':
        if (empty($driveId)) {
            respond(['success' => false, 'mensaje' => 'No hay respaldo en Drive para restaurar'], 400);
        }

        try {
            $driveClient = new GoogleDriveClient();
        } catch (Throwable $e) {
            respond(['success' => false, 'mensaje' => 'No se pudo inicializar Google Drive: ' . $e->getMessage()], 500);
        }

        if (!$driveClient->isEnabled()) {
            respond(['success' => false, 'mensaje' => 'Google Drive no está habilitado en el sistema'], 500);
        }


        // Get the original filename from Drive
        $driveMetadata = $driveClient->getFileMetadata($driveId);
        $originalName = $driveMetadata['name'] ?? null;

        $relativePath = $localFileName;
        if (empty($relativePath) && !empty($originalName)) {
            // Use the original Drive filename
            $relativePath = $originalName;
        } elseif (empty($relativePath)) {
            // Fallback to generic name if no Drive metadata
            $relativePath = sprintf('expediente_%s_%s.pdf', $idSlug, date('Ymd_His'));
        }

        // Always use just the filename, ignore any directory in the stored path
        $baseName = basename($relativePath);
        $safeBase = pathinfo($baseName, PATHINFO_FILENAME);
        $extension = pathinfo($baseName, PATHINFO_EXTENSION) ?: 'pdf';
        $candidate = $safeBase . '.' . $extension;

        // Determine the correct subfolder based on career using DriveFolderMapper
        $directoryPart = '';

        // Get the career title from egresado table
        $sqlTitulo = "SELECT tituloObtenido FROM egresado WHERE identificacion = :id LIMIT 1";
        $queryTitulo = $egresado->acceso->prepare($sqlTitulo);
        $queryTitulo->execute([':id' => $id]);
        $rowTitulo = $queryTitulo->fetch(PDO::FETCH_ASSOC);

        $carrera = $rowTitulo['tituloobtenido'] ?? $rowTitulo['tituloObtenido'] ?? null;

        if (!empty($carrera)) {
            // Use DriveFolderMapper to get the correct local folder
            $mapping = DriveFolderMapper::resolveByTitle($carrera);
            $folderName = $mapping['local_folder'] ?? '';

            if (!empty($folderName)) {
                $directoryPart = $folderName;
            }
        }
        // If no career title found, file will be saved in root directory (fallback)

        $targetDir = $dir;
        if ($directoryPart !== '') {
            $targetDir .= DIRECTORY_SEPARATOR . $directoryPart;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
        }

        $counter = 1;
        while (is_file($targetDir . DIRECTORY_SEPARATOR . $candidate)) {
            $candidate = sprintf('%s_%d.%s', $safeBase, $counter, $extension);
            $counter++;
        }
        $destPath = $targetDir . DIRECTORY_SEPARATOR . $candidate;
        $relativeCandidate = $directoryPart !== ''
            ? $directoryPart . '/' . $candidate
            : $candidate;

        if (!$driveClient->downloadFile($driveId, $destPath)) {
            respond(['success' => false, 'mensaje' => 'No se pudo descargar el archivo desde Google Drive'], 500);
        }

        $egresado->ActualizarExpedienteStorage($id, ['archivo' => str_replace('\\', '/', $relativeCandidate)]);

        respond([
            'success' => true,
            'mensaje' => 'Copia local restaurada correctamente',
            'local_url' => buildLocalUrl($relativeCandidate),
        ]);
        break;

    case 'subir_drive':
        if (!$localExists) {
            respond(['success' => false, 'mensaje' => 'No existe una copia local para subir a Drive'], 400);
        }

        try {
            $driveClient = new GoogleDriveClient();
        } catch (Throwable $e) {
            respond(['success' => false, 'mensaje' => 'No se pudo inicializar Google Drive: ' . $e->getMessage()], 500);
        }

        if (!$driveClient->isEnabled()) {
            respond(['success' => false, 'mensaje' => 'Google Drive no está habilitado en el sistema'], 500);
        }

        // Get career to determine correct Drive folder
        $targetDriveFolderId = null;
        $sqlCarrera = "SELECT tituloObtenido FROM egresado WHERE identificacion = :id LIMIT 1";
        $queryCarrera = $egresado->acceso->prepare($sqlCarrera);
        $queryCarrera->execute([':id' => $id]);
        $rowCarrera = $queryCarrera->fetch(PDO::FETCH_ASSOC);

        if ($rowCarrera) {
            $tituloDb = $rowCarrera['tituloobtenido'] ?? $rowCarrera['tituloObtenido'] ?? null;
            if (!empty($tituloDb)) {
                $mapping = DriveFolderMapper::resolveByTitle($tituloDb);
                $targetDriveFolderId = $mapping['drive_folder_id'] ?? null;
            }
        }

        $uploadResult = $driveClient->uploadFile($localPath, basename($localPath), 'application/pdf', $targetDriveFolderId);
        $newDriveId = $uploadResult['id'] ?? null;
        if (empty($newDriveId)) {
            respond(['success' => false, 'mensaje' => 'Google Drive no devolvió un ID de archivo'], 500);
        }

        $newDriveLink = $uploadResult['webViewLink'] ?? $uploadResult['webContentLink'] ?? null;
        $egresado->ActualizarExpedienteStorage($id, [
            'drive_id' => $newDriveId,
            'drive_link' => $newDriveLink,
        ]);

        // Trigger automatic sync in background
        try {
            triggerDriveSync();
        } catch (Throwable $e) {
            error_log('Auto-sync failed: ' . $e->getMessage());
        }

        respond([
            'success' => true,
            'mensaje' => 'Expediente respaldado en Google Drive',
            'drive_id' => $newDriveId,
            'drive_url' => $newDriveLink,
        ]);
        break;

    default:
        respond(['success' => false, 'mensaje' => 'Acción no reconocida'], 400);
}
