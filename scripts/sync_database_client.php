<?php
/**
 * Sincronizar base de datos desde PC maestro
 * Para PCs clientes
 */

date_default_timezone_set('America/Santiago');

// Parse argumentos
$options = getopt('', ['source:', 'file:']);
$source = $options['source'] ?? null;
$file = $options['file'] ?? null;

echo "========================================\n";
echo "   SINCRONIZAR BASE DE DATOS\n";
echo "========================================\n\n";

// Determinar fuente
if ($file) {
   $sqlFile = $file;
    echo "Modo: Archivo local\n";
    echo "Archivo: $sqlFile\n\n";
} elseif ($source) {
    echo "Modo: Carpeta compartida\n";
    echo "Carpeta: $source\n\n";
    
    // Buscar último archivo SQL
    if (!is_dir($source)) {
        echo "❌ ERROR: No se puede acceder a $source\n";
        exit(1);
    }
    
    $files = glob($source . '/gestion_egresados_*.sql');
    if (empty($files)) {
        echo "❌ ERROR: No se encontraron archivos SQL en $source\n";
        exit(1);
    }
    
    // Ordenar por fecha (más reciente primero)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $sqlFile = $files[0];
    $filename = basename($sqlFile);
    $modifiedTime = date('Y-m-d H:i:s', filemtime($sqlFile));
    
    echo "📁 Archivo más reciente: $filename\n";
    echo "⏰ Fecha: $modifiedTime\n\n";
} else {
    echo "❌ ERROR: Debes especificar --source o --file\n";
    echo "Uso:\n";
    echo "  php sync_database_client.php --source=\"\\\\192.168.1.102\\EGRESAPP_BD\"\n";
    echo "  php sync_database_client.php --file=\"ruta/al/archivo.sql\"\n";
    exit(1);
}

// Verificar archivo
if (!file_exists($sqlFile)) {
    echo "❌ ERROR: Archivo no encontrado: $sqlFile\n";
    exit(1);
}

$size = filesize($sqlFile);
$sizeKB = round($size / 1024, 2);
echo "📊 Tamaño: $sizeKB KB\n\n";

// Confirmar
echo "========================================\n";
echo "⚠️  ADVERTENCIA\n";
echo "========================================\n";
echo "Esta acción sobrescribirá tu base de datos local.\n";
echo "Se creará un backup antes de importar.\n\n";
echo "¿Continuar? (s/n): ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 's') {
    echo "\n❌ Sincronización cancelada\n";
    exit(0);
}

// Configuración
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'gestion_egresados',
    'username' => 'root',
    'password' => '',
];

// Crear backup antes de importar
echo "\n🔄 Creando backup de seguridad...\n";
$backupDir = __DIR__ . '/../db_backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupFile = $backupDir . '/backup_before_sync_' . date('Ymd_His') . '.sql';

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

if ($mysqldump) {
    $backupCmd = sprintf(
        '"%s" --host=%s --port=%d --user=%s %s %s > "%s" 2>&1',
        $mysqldump,
        $config['host'],
        $config['port'],
        $config['username'],
        $config['password'] ? '--password=' . escapeshellarg($config['password']) : '',
        $config['database'],
        $backupFile
    );
    
    exec($backupCmd, $output, $returnCode);
    if ($returnCode === 0 && file_exists($backupFile)) {
        echo "✅ Backup creado: $backupFile\n\n";
    } else {
        echo "⚠️  No se pudo crear backup (continuando de todas formas)\n\n";
    }
} else {
    echo "⚠️  mysqldump no encontrado (continuando sin backup)\n\n";
}

// Encontrar mysql
$mysqlPaths = [
    'C:/laragon/bin/mysql/mysql-8.0.30/bin/mysql.exe',
    'C:/laragon/bin/mysql/mysql-5.7.33/bin/mysql.exe',
    'C:/laragon/bin/mysql/mysql-5.7/bin/mysql.exe',
];

$mysql = null;
foreach ($mysqlPaths as $path) {
    if (file_exists($path)) {
        $mysql = $path;
        break;
    }
}

if (!$mysql) {
    $laravelMysqlDir = 'C:/laragon/bin/mysql';
    if (is_dir($laravelMysqlDir)) {
        $dirs = glob($laravelMysqlDir . '/mysql-*');
        foreach ($dirs as $dir) {
            $testPath = $dir . '/bin/mysql.exe';
            if (file_exists($testPath)) {
                $mysql = $testPath;
                break;
            }
        }
    }
}

if (!$mysql) {
    echo "❌ ERROR: No se encontró mysql.exe\n";
    exit(1);
}

// Importar
echo "🔄 Importando base de datos...\n";
echo "   Esto puede tomar un momento...\n\n";

$importCmd = sprintf(
    '"%s" --host=%s --port=%d --user=%s %s %s < "%s" 2>&1',
    $mysql,
    $config['host'],
    $config['port'],
    $config['username'],
    $config['password'] ? '--password=' . escapeshellarg($config['password']) : '',
    $config['database'],
    $sqlFile
);

exec($importCmd, $output, $returnCode);

if ($returnCode !== 0) {
    echo "========================================\n";
    echo "❌ ERROR AL IMPORTAR\n";
    echo "========================================\n\n";
    echo implode("\n", $output) . "\n";
    
    if (file_exists($backupFile)) {
        echo "\n💡 RECUPERACIÓN: Se creó un backup en:\n";
        echo "   $backupFile\n";
        echo "   Puedes restaurarlo si algo salió mal.\n";
    }
    
    exit(1);
}

// Registrar última sincronización
$syncInfoFile = __DIR__ . '/../db_backups/last_sync.json';
$syncInfo = [
    'synced_at' => date('Y-m-d H:i:s'),
    'source_file' => basename($sqlFile),
    'size_bytes' => $size,
    'hostname' => gethostname(),
];
file_put_contents($syncInfoFile, json_encode($syncInfo, JSON_PRETTY_PRINT));

echo "========================================\n";
echo "✅ SINCRONIZACIÓN EXITOSA\n";
echo "========================================\n\n";
echo "📁 Archivo importado: " . basename($sqlFile) . "\n";
echo "⏰ Sincronizado: " . date('Y-m-d H:i:s') . "\n\n";

if (file_exists($backupFile)) {
    echo "💾 Backup guardado en:\n";
    echo "   $backupFile\n\n";
}

echo "🎉 Tu base de datos está actualizada\n\n";

exit(0);
