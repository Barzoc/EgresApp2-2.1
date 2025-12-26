# ============================================================================
# TEST DE CONEXIÓN A BASE DE DATOS - EGRESAPP2
# ============================================================================
# Verifica la conexión desde PHP a MySQL
# ============================================================================

param(
    [string]$ProjectRoot = "C:\laragon\www\EGRESAPP2"
)

Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      TEST DE CONEXIÓN A BASE DE DATOS - PHP          ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Crear script de test temporal
$testScript = @'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════\n";
echo "  TEST DE CONEXIÓN A BASE DE DATOS\n";
echo "═══════════════════════════════════════════════════════\n\n";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'gestion_egresados';

// Test 1: MySQLi
echo "1. TEST MYSQLI:\n";
try {
    $mysqli = new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_error) {
        echo "  ✗ Error: " . $mysqli->connect_error . "\n";
    } else {
        echo "  ✓ Conexión MySQLi exitosa\n";
        echo "  Versión MySQL: " . $mysqli->server_info . "\n";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "  ✗ Excepción: " . $e->getMessage() . "\n";
}

echo "\n2. TEST PDO:\n";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    echo "  ✓ Conexión PDO exitosa\n";
    
    // Test consulta
    echo "\n3. TEST CONSULTA:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  Tablas encontradas: " . count($tables) . "\n";
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "    • $table: $count registros\n";
    }
    
    // Test específico tabla egresado
    echo "\n4. TEST TABLA EGRESADO:\n";
    $egresados = $pdo->query("SELECT COUNT(*) FROM egresado")->fetchColumn();
    echo "  Total de egresados: $egresados\n";
    
    echo "\n═══════════════════════════════════════════════════════\n";
    echo "  ✅ TODAS LAS PRUEBAS EXITOSAS\n";
    echo "═══════════════════════════════════════════════════════\n";
    
} catch (PDOException $e) {
    echo "  ✗ Error PDO: " . $e->getMessage() . "\n";
}
?>
'@

$testFile = "$ProjectRoot\test_conexion_db_temp.php"
$testScript | Out-File -FilePath $testFile -Encoding UTF8

# Buscar PHP
$phpPaths = @(
    "C:\laragon\bin\php\php-8.0.30\php.exe",
    "C:\laragon\bin\php\php-8.1.0\php.exe",
    "C:\xampp\php\php.exe"
)

$phpExe = $null
foreach ($path in $phpPaths) {
    if (Test-Path $path) {
        $phpExe = $path
        break
    }
}

if (-not $phpExe) {
    Write-Host "✗ PHP no encontrado" -ForegroundColor Red
    exit 1
}

# Ejecutar test
Write-Host "Ejecutando test de conexión..." -ForegroundColor Cyan
Write-Host ""

& $phpExe $testFile

# Limpiar
Remove-Item $testFile -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Presione cualquier tecla para continuar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
