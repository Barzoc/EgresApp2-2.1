<?php
/**
 * Exportar base de datos a archivo SQL
 * Para sincronización entre PCs
 */

date_default_timezone_set('America/Santiago');

// Configuración
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'gestion_egresados',
    'username' => 'root',
    'password' => '',
];

$exportDir = __DIR__ . '/../db_exports';
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

$timestamp = date('Ymd_His');
$filename = "{$config['database']}_{$timestamp}.sql";
$filepath = "$exportDir/$filename";

echo "========================================\n";
echo "   EXPORTAR BASE DE DATOS\n";
echo "========================================\n\n";
echo "Base de datos: {$config['database']}\n";
echo "Archivo: $filename\n\n";

// Encontrar mysqldump
$mysqldumpPaths = [
    'C:/laragon/bin/mysql/mysql-8.0.30/bin/mysqldump.exe',
    'C:/laragon/bin/mysql/mysql-5.7.33/bin/mysqldump.exe',
    'C:/laragon/bin/mysql/mysql-5.7/bin/mysqldump.exe',
];

$mysqldump = null;
foreach ($mysqldumpPaths as $path) {
    if (file_exists($path)) {
        $mysqldump = $path;
        break;
    }
}

// Buscar en directorios de Laragon
if (!$mysqldump) {
    $laravelMysqlDir = 'C:/laragon/bin/mysql';
    if (is_dir($laravelMysqlDir)) {
        $dirs = glob($laravelMysqlDir . '/mysql-*');
        foreach ($dirs as $dir) {
            $testPath = $dir . '/bin/mysqldump.exe';
            if (file_exists($testPath)) {
                $mysqldump = $testPath;
                break;
            }
        }
    }
}

if (!$mysqldump) {
    echo "❌ ERROR: No se encontró mysqldump\n";
    echo "Buscado en:\n";
    foreach ($mysqldumpPaths as $path) {
        echo "  - $path\n";
    }
    exit(1);
}

echo "🔄 Exportando usando: $mysqldump\n\n";

// Comando mysqldump
$command = sprintf(
    '"%s" --host=%s --port=%d --user=%s %s %s > "%s" 2>&1',
    $mysqldump,
    $config['host'],
    $config['port'],
    $config['username'],
    $config['password'] ? '--password=' . escapeshellarg($config['password']) : '',
    $config['database'],
    $filepath
);

// Ejecutar
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "❌ ERROR al exportar:\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

// Verificar archivo creado
if (!file_exists($filepath) || filesize($filepath) == 0) {
    echo "❌ ERROR: El archivo no se creó o está vacío\n";
    exit(1);
}

$size = filesize($filepath);
$sizeKB = round($size / 1024, 2);

echo "========================================\n";
echo "✅ EXPORTACIÓN EXITOSA\n";
echo "========================================\n\n";
echo "📁 Archivo: $filepath\n";
echo "📊 Tamaño: $sizeKB KB\n";
echo "⏰ Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Crear metadata
$metadata = [
    'filename' => $filename,
    'database' => $config['database'],
    'exported_at' => date('Y-m-d H:i:s'),
    'size_bytes' => $size,
    'host' => gethostname(),
];

file_put_contents("$exportDir/last_export.json", json_encode($metadata, JSON_PRETTY_PRINT));

exit(0);
