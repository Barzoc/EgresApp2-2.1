<?php

class DriveFolderMapper
{
    private static ?array $entries = null;

    private static function loadEntries(): array
    {
        if (self::$entries !== null) {
            return self::$entries;
        }

        $configPath = __DIR__ . '/../config/drive_folders.php';
        $raw = is_file($configPath) ? require $configPath : [];
        $entries = [];

        foreach ($raw as $entry) {
            if (empty($entry['drive_folder_id'])) {
                continue;
            }

            $aliases = $entry['aliases'] ?? [];
            if (is_string($aliases)) {
                $aliases = [$aliases];
            }
            $normalizedAliases = array_filter(array_map([self::class, 'normalizeKey'], $aliases));

            $entries[] = [
                'aliases' => $normalizedAliases,
                'drive_folder_id' => $entry['drive_folder_id'],
                'local_folder' => trim((string) ($entry['local_folder'] ?? ''), '/\\'),
                'label' => $aliases[0] ?? $entry['drive_folder_id'],
            ];
        }

        self::$entries = $entries;
        return self::$entries;
    }

    private static function normalizeKey(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        // Reemplazar acentos españoles manualmente para mejor compatibilidad
        $replacements = [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ñ' => 'N',
            'á' => 'A',
            'é' => 'E',
            'í' => 'I',
            'ó' => 'O',
            'ú' => 'U',
            'ñ' => 'N',
            // Common OCR errors
            '6' => 'O',
            '0' => 'O',
        ];
        $value = strtr($value, $replacements);

        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9 ]+/u', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        // Fix specific keywords
        $value = str_replace(
            [
                'ADMINISTRACIEN',
                'ADMINISTRACI6N',
                'ADMINISTRACIN',
                'OPERACI6N',
                'OPERACIEN',
                'OPERACIN',
                'OPERACION PORTUARIA',
                'OPERACIONES PORTUARIA',
                'OPERACIEN PORTUARIA',
                'COMPUTACI6N',
                'COMPUTACIEN',
                'IMPORTACIEN',
                'EXPORTACIEN'
            ],
            [
                'ADMINISTRACION',
                'ADMINISTRACION',
                'ADMINISTRACION',
                'OPERACIONES',
                'OPERACION',
                'OPERACION',
                'OPERACIONES PORTUARIAS',
                'OPERACIONES PORTUARIAS',
                'OPERACIONES PORTUARIAS',
                'COMPUTACION',
                'COMPUTACION',
                'IMPORTACION',
                'EXPORTACION'
            ],
            $value
        );

        // Remove 'DE NIVEL MEDIO' to match simpler aliases
        $value = str_replace('DE NIVEL MEDIO', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    public static function resolveByTitle(?string $title): array
    {
        $key = self::normalizeKey($title);
        $default = [
            'drive_folder_id' => null,
            'local_folder' => '',
            'label' => null,
        ];

        if ($key === '') {
            return $default;
        }

        foreach (self::loadEntries() as $entry) {
            // Exact match
            if (in_array($key, $entry['aliases'], true)) {
                return $entry;
            }
        }

        // Fuzzy match: check if key contains alias or alias contains key (for long titles vs short aliases)
        foreach (self::loadEntries() as $entry) {
            foreach ($entry['aliases'] as $alias) {
                if ($alias !== '' && (strpos($key, $alias) !== false || strpos($alias, $key) !== false)) {
                    return $entry;
                }
            }
        }

        // Buscar un default explícito (ej: EXPEDIENTES ALUMNOS) o usar el root del config
        $defaultId = null;
        $entries = self::loadEntries();

        // Estrategia: Buscar entrada con alias 'EXPEDIENTES ALUMNOS' o 'ROOT'
        foreach ($entries as $entry) {
            foreach ($entry['aliases'] as $alias) {
                if (in_array(strtoupper($alias), ['EXPEDIENTES ALUMNOS', 'RAIZ', 'ROOT'], true)) {
                    $defaultId = $entry['drive_folder_id'];
                    break 2;
                }
            }
        }

        // Si no hay default explicito, usar null (root)
        $default['drive_folder_id'] = $defaultId;

        if ($key === '') {
            return $default;
        }

        foreach ($entries as $entry) {
            if (in_array($key, $entry['aliases'], true)) {
                return $entry;
            }
        }

        // Si no encuentro match específico, retorno el default encontrado
        return $default;
    }

    public static function getAll(): array
    {
        return self::loadEntries();
    }

    public static function getEntryByDriveFolderId(?string $folderId): ?array
    {
        $folderId = trim((string) $folderId);
        if ($folderId === '') {
            return null;
        }

        foreach (self::loadEntries() as $entry) {
            if (($entry['drive_folder_id'] ?? null) === $folderId) {
                return $entry;
            }
        }

        return null;
    }

    public static function ensureLocalDirectory(string $baseDir, ?string $subFolder): string
    {
        $subFolder = trim((string) $subFolder, '/\\');
        if ($subFolder === '') {
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0755, true);
            }
            return '';
        }

        $target = $baseDir . DIRECTORY_SEPARATOR . $subFolder;
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        return $subFolder;
    }
}
