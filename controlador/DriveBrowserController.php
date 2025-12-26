<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';

function drive_browser_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function drive_browser_base_dir(): string
{
    static $baseDir = null;
    if ($baseDir !== null) {
        return $baseDir;
    }

    $resolved = realpath(__DIR__ . '/../assets/expedientes/expedientes_subidos');
    if ($resolved === false) {
        $resolved = __DIR__ . '/../assets/expedientes/expedientes_subidos';
    }

    $baseDir = $resolved;
    return $baseDir;
}

function drive_browser_resolve_local_dir(?string $relativeFolder): array
{
    $baseDir = drive_browser_base_dir();
    $relativeFolder = trim((string) $relativeFolder, '/\\');
    if ($relativeFolder === '') {
        return [$baseDir, false];
    }

    $candidate = $baseDir . DIRECTORY_SEPARATOR . $relativeFolder;
    if (is_dir($candidate)) {
        return [$candidate, true];
    }

    return [$baseDir, false];
}

function drive_browser_build_local_index(string $directory, ?string $baseDir = null): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
    );

    $base = $baseDir ?? $directory;
    $baseReal = realpath($base) ?: $base;
    $baseNormalized = rtrim(str_replace('\\', '/', $baseReal), '/') . '/';

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $fullPath = str_replace('\\', '/', $fileInfo->getPathname());
            $relative = $fileInfo->getFilename();
            if (strpos($fullPath, $baseNormalized) === 0) {
                $relative = ltrim(substr($fullPath, strlen($baseNormalized)), '/');
            }

            $relative = str_replace('\\', '/', $relative);
            $filename = strtolower($fileInfo->getFilename());
            $normalized = drive_browser_normalize_filename($fileInfo->getFilename());

            $files[$filename] = $relative;
            $files[$normalized] = $relative;
        }
    }

    return $files;
}

function drive_browser_normalize_filename(?string $name): string
{
    $name = (string) $name;
    if ($name === '') {
        return '';
    }

    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'pdf');
    $base = pathinfo($name, PATHINFO_FILENAME);

    $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', $base);
    $safeBase = preg_replace('/_+/', '_', $safeBase ?? '') ?: 'expediente';

    return strtolower($safeBase . '.' . $extension);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    drive_browser_response([
        'success' => false,
        'mensaje' => 'Método no permitido. Usa POST.',
    ], 405);
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar_archivos':
        $folderId = trim($_POST['folder_id'] ?? '');
        $includeFolders = filter_var($_POST['include_folders'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($folderId === '') {
            drive_browser_response([
                'success' => false,
                'mensaje' => 'Debes proporcionar el ID de la carpeta.',
            ], 400);
        }

        try {
            $driveClient = new GoogleDriveClient();
        } catch (Throwable $e) {
            drive_browser_response([
                'success' => false,
                'mensaje' => 'No se pudo inicializar Google Drive: ' . $e->getMessage(),
            ], 500);
        }

        if (!$driveClient->isEnabled()) {
            drive_browser_response([
                'success' => false,
                'mensaje' => 'Google Drive no está habilitado en la aplicación.',
            ], 500);
        }

        $archivos = $driveClient->listFolderFiles($folderId, $includeFolders);
        usort($archivos, static function ($a, $b) {
            $nameA = strtoupper($a['name'] ?? '');
            $nameB = strtoupper($b['name'] ?? '');
            if ($nameA === $nameB) {
                return 0;
            }
            return $nameA < $nameB ? -1 : 1;
        });

        $mapperEntry = DriveFolderMapper::getEntryByDriveFolderId($folderId);
        [$localDir, $hasSpecificFolder] = drive_browser_resolve_local_dir($mapperEntry['local_folder'] ?? null);
        $baseDir = drive_browser_base_dir();
        $localIndex = drive_browser_build_local_index($localDir, $baseDir);
        $baseIndex = (!$hasSpecificFolder || $localDir === $baseDir)
            ? []
            : drive_browser_build_local_index($baseDir, $baseDir);

        foreach ($archivos as &$archivo) {
            $archivo['respaldo_local'] = false;
            if (!empty($archivo['isFolder'])) {
                continue;
            }

            $name = strtolower($archivo['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $normalized = drive_browser_normalize_filename($archivo['name'] ?? '');
            $matchedRelative = $localIndex[$name]
                ?? $localIndex[$normalized]
                ?? ($baseIndex[$name] ?? $baseIndex[$normalized] ?? null);

            if ($matchedRelative !== null) {
                $archivo['respaldo_local'] = true;
                $archivo['local_path'] = $matchedRelative;
            }
        }
        unset($archivo);

        drive_browser_response([
            'success' => true,
            'folder_id' => $folderId,
            'total' => count($archivos),
            'archivos' => $archivos,
        ]);
        break;

    default:
        drive_browser_response([
            'success' => false,
            'mensaje' => 'Acción no reconocida.',
        ], 400);
}
