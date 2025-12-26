<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class GoogleDriveClient
{
    private bool $enabled = false;
    private ?Drive $service = null;
    private ?string $folderId = null;
    private array $config = [];

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->loadConfig();
        $this->enabled = (bool) ($this->config['enabled'] ?? false);

        if (!$this->enabled) {
            return;
        }

        $this->folderId = $this->config['folder_id'] ?? null;
        if (empty($this->folderId)) {
            throw new RuntimeException('No se configuró el folder_id de Google Drive.');
        }

        $this->service = new Drive($this->getClient());
    }

    public function getClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName('Prototipo QR Sync');
        $client->setScopes([Drive::DRIVE]);
        $client->setAuthConfig(__DIR__ . '/../config/client_secret.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Fix SSL error for local environment (Laragon)
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);
        $client->setHttpClient($httpClient);

        // Load previously authorized token from a file, if it exists.
        $tokenPath = __DIR__ . '/../config/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // This state requires user interaction to get a new token.
                // We cannot do this non-interactively if we don't have a token.
                // The calling script must handle this or we throw an exception.
                if (php_sapi_name() === 'cli') {
                    $this->requestNewToken($client, $tokenPath);
                } else {
                    throw new RuntimeException('Se requiere autorización OAuth. Ejecute el script de sincronización desde la terminal.');
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    private function requestNewToken(GoogleClient $client, string $tokenPath): void
    {
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        // Check for errors
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }
    }

    private function loadConfig(): array
    {
        $path = __DIR__ . '/../config/drive.php';
        if (is_file($path)) {
            return require $path;
        }
        return [];
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->service instanceof Drive;
    }

    public function uploadFile(string $localPath, string $fileName, string $mimeType = 'application/pdf', ?string $folderId = null): array
    {
        if (!$this->isEnabled()) {
            throw new RuntimeException('Google Drive no está habilitado.');
        }

        if (!is_file($localPath)) {
            throw new RuntimeException('El archivo local no existe: ' . $localPath);
        }

        $parentId = $folderId ?: $this->folderId;

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$parentId],
        ]);

        $file = $this->service->files->create($fileMetadata, [
            'data' => file_get_contents($localPath),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink, parents',
            'supportsAllDrives' => true,
        ]);

        // Permitir que cualquier persona con el enlace pueda verlo.
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $this->service->permissions->create($file->id, $permission, ['sendNotificationEmail' => false]);

        return [
            'id' => $file->id,
            'name' => $file->name,
            'webViewLink' => $file->webViewLink ?? null,
            'webContentLink' => $file->webContentLink ?? null,
            'parents' => $file->parents,
        ];
    }

    public function moveFileToFolder(string $fileId, string $targetFolderId): bool
    {
        if (!$this->isEnabled() || empty($fileId) || empty($targetFolderId)) {
            return false;
        }

        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'parents',
                'supportsAllDrives' => true,
            ]);
            $previousParents = $file->getParents();
            $removeParents = $previousParents ? implode(',', $previousParents) : null;

            $params = [
                'addParents' => $targetFolderId,
                'supportsAllDrives' => true,
            ];

            if (!empty($removeParents)) {
                $params['removeParents'] = $removeParents;
            }

            $this->service->files->update($fileId, new DriveFile(), $params);
            return true;
        } catch (Throwable $e) {
            error_log('GoogleDriveClient moveFileToFolder error: ' . $e->getMessage());
            return false;
        }
    }

    public function getRootFolderId(): ?string
    {
        return $this->folderId;
    }

    public function getFileMetadata(string $fileId): ?array
    {
        if (!$this->isEnabled() || empty($fileId)) {
            return null;
        }

        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'id, name, webViewLink, webContentLink, trashed, parents, mimeType',
                'supportsAllDrives' => true,
            ]);
            if ($file->trashed) {
                return null;
            }
            return [
                'id' => $file->id,
                'name' => $file->name,
                'webViewLink' => $file->webViewLink,
                'webContentLink' => $file->webContentLink,
                'mimeType' => $file->mimeType,
                'parents' => $file->parents,
            ];
        } catch (Throwable $e) {
            error_log('GoogleDriveClient metadata error: ' . $e->getMessage());
            return null;
        }
    }

    public function downloadFile(string $fileId, string $destinationPath): bool
    {
        if (!$this->isEnabled() || empty($fileId)) {
            return false;
        }

        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'id, mimeType',
                'supportsAllDrives' => true,
            ]);
        } catch (Throwable $e) {
            error_log('GoogleDriveClient download metadata error: ' . $e->getMessage());
            return false;
        }

        $mimeType = $file->getMimeType();
        $response = null;

        if (is_string($mimeType) && str_starts_with($mimeType, 'application/vnd.google-apps.')) {
            $exportMime = $this->getExportMimeType($mimeType);
            if ($exportMime === null) {
                throw new RuntimeException('Este archivo de Google no puede exportarse automáticamente (tipo: ' . $mimeType . ').');
            }

            try {
                $response = $this->service->files->export($fileId, $exportMime, [
                    'supportsAllDrives' => true,
                ]);
            } catch (Throwable $e) {
                error_log('GoogleDriveClient export error: ' . $e->getMessage());
                throw new RuntimeException('No se pudo exportar el archivo de Google a PDF.');
            }
        } else {
            $response = $this->service->files->get($fileId, [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);
        }

        $data = $response->getBody()->getContents();
        if ($data === false) {
            return false;
        }

        $dir = dirname($destinationPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($destinationPath, $data) !== false;
    }

    private function getExportMimeType(?string $googleMimeType): ?string
    {
        return match ($googleMimeType) {
            'application/vnd.google-apps.document',
            'application/vnd.google-apps.presentation',
            'application/vnd.google-apps.drawing',
            'application/vnd.google-apps.spreadsheet' => 'application/pdf',
            default => null,
        };
    }

    public function deleteFile(string $fileId): bool
    {
        if (!$this->isEnabled() || empty($fileId)) {
            return false;
        }

        try {
            $this->service->files->delete($fileId, ['supportsAllDrives' => true]);
            return true;
        } catch (Throwable $e) {
            error_log('GoogleDriveClient delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function listFiles(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $files = [];
        $pageToken = null;

        do {
            try {
                $response = $this->service->files->listFiles([
                    'q' => "'" . $this->folderId . "' in parents and trashed = false",
                    'fields' => 'nextPageToken, files(id, name)',
                    'pageToken' => $pageToken,
                    'pageSize' => 1000,
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ]);

                foreach ($response->getFiles() as $file) {
                    $files[$file->getName()] = $file->getId();
                }

                $pageToken = $response->getNextPageToken();
            } catch (Throwable $e) {
                error_log('GoogleDriveClient listFiles error: ' . $e->getMessage());
                break;
            }
        } while ($pageToken !== null);

        return $files;
    }

    public function findFileByName(string $fileName): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $fileName = trim($fileName);
        if ($fileName === '') {
            return null;
        }

        // Extract basename without extension for more flexible matching
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $escaped = addcslashes($baseName, "'\\");

        // Use 'contains' instead of exact match for more flexibility
        $query = sprintf("name contains '%s' and trashed = false", $escaped);

        try {
            $response = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name, parents, webViewLink, webContentLink)',
                'pageSize' => 1,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            $files = $response->getFiles();
            if (empty($files)) {
                return null;
            }

            $file = $files[0];
            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'parents' => $file->getParents(),
                'webViewLink' => $file->getWebViewLink(),
                'webContentLink' => $file->getWebContentLink(),
            ];
        } catch (Throwable $e) {
            error_log('GoogleDriveClient findFileByName error: ' . $e->getMessage());
            return null;
        }
    }

    public function listFolderFiles(string $folderId, bool $includeFolders = false): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $folderId = trim($folderId);
        if ($folderId === '') {
            return [];
        }

        $items = $this->listFolderItems($folderId);
        $files = [];

        foreach ($items as $item) {
            $mime = $item['mimeType'] ?? '';
            $isFolder = ($mime === 'application/vnd.google-apps.folder');

            if (!$includeFolders && $isFolder) {
                continue;
            }

            $files[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? '',
                'mimeType' => $mime,
                'modifiedTime' => $item['modifiedTime'] ?? null,
                'webViewLink' => $item['webViewLink'] ?? null,
                'isFolder' => $isFolder,
                'descargable' => !$isFolder,
            ];
        }

        return $files;
    }

    private function listFolderItems(string $folderId, ?string $extraFilter = null): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $items = [];
        $pageToken = null;
        $queryBase = sprintf("'%s' in parents and trashed = false", $folderId);
        if (!empty($extraFilter)) {
            $queryBase .= ' ' . $extraFilter;
        }

        do {
            try {
                $response = $this->service->files->listFiles([
                    'q' => $queryBase,
                    'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, webViewLink)',
                    'pageToken' => $pageToken,
                    'pageSize' => 1000,
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ]);

                foreach ($response->getFiles() as $file) {
                    $items[] = [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'mimeType' => $file->getMimeType(),
                        'modifiedTime' => $file->getModifiedTime(),
                        'webViewLink' => $file->getWebViewLink(),
                    ];
                }

                $pageToken = $response->getNextPageToken();
            } catch (Throwable $e) {
                error_log('GoogleDriveClient listFolderItems error: ' . $e->getMessage());
                break;
            }
        } while ($pageToken !== null);

        return $items;
    }

    private function countFilesInFolder(string $folderId): int
    {
        $extraFilter = "and mimeType != 'application/vnd.google-apps.folder'";
        $items = $this->listFolderItems($folderId, $extraFilter);
        return count($items);
    }

    public function getFolderSummary(): array
    {
        if (!$this->isEnabled()) {
            return [
                'habilitado' => false,
                'total_archivos' => 0,
                'carpetas' => [],
                'mensaje' => 'Google Drive no está habilitado.',
            ];
        }

        $summary = [
            'habilitado' => true,
            'total_archivos' => 0,
            'carpetas' => [],
            'timestamp' => date('c'),
        ];

        $children = $this->listFolderItems($this->folderId);
        $rootFiles = 0;

        foreach ($children as $child) {
            if (($child['mimeType'] ?? '') === 'application/vnd.google-apps.folder') {
                $count = $this->countFilesInFolder($child['id']);
                $summary['carpetas'][] = [
                    'id' => $child['id'],
                    'nombre' => $child['name'],
                    'total_archivos' => $count,
                ];
                $summary['total_archivos'] += $count;
            } else {
                $rootFiles++;
            }
        }

        if ($rootFiles > 0) {
            $summary['carpetas'][] = [
                'id' => $this->folderId,
                'nombre' => 'Raíz',
                'total_archivos' => $rootFiles,
            ];
            $summary['total_archivos'] += $rootFiles;
        }

        usort($summary['carpetas'], function ($a, $b) {
            return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
        });

        return $summary;
    }
}
