# ============================================================================
# CHECKLIST 2: VERIFICACIÓN DE BASE DE DATOS - EGRESAPP2
# ============================================================================

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CHECKLIST 2: VERIFICACIÓN DE BASE DE DATOS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$mysqlLocations = @(
    "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe",
    "C:\xampp\mysql\bin\mysql.exe"
)

$MYSQL_EXE = $null
foreach ($location in $mysqlLocations) {
    if (Test-Path $location) {
        $MYSQL_EXE = $location
        break
    }
}

if (-not $MYSQL_EXE) {
    Write-Host "❌ MySQL no encontrado" -ForegroundColor Red
    return $false
}

$dbName = "gestion_egresados"

# 2.1 Base de datos existe
$dbExists = & $MYSQL_EXE -u root -e "SHOW DATABASES LIKE '$dbName';" 2>&1
if ($dbExists -match $dbName) {
    Write-Host "✅ Base de datos '$dbName' existe" -ForegroundColor Green
} else {
    Write-Host "❌ Base de datos '$dbName' NO existe" -ForegroundColor Red
    return $false
}

# 2.2 Tablas críticas presentes
$expectedTables = @("egresado", "titulo", "tituloegresado", "usuario")
$tables = & $MYSQL_EXE -u root $dbName -e "SHOW TABLES;" 2>&1 | Select-Object -Skip 1

$allTablesOk = $true
foreach ($table in $expectedTables) {
    if ($tables -contains $table) {
        $count = & $MYSQL_EXE -u root $dbName -e "SELECT COUNT(*) FROM $table;" 2>&1 | Select-Object -Skip 1
        Write-Host "✅ Tabla '$table' presente ($count registros)" -ForegroundColor Green
    } else {
        Write-Host "❌ Tabla '$table' FALTANTE" -ForegroundColor Red
        $allTablesOk = $false
    }
}

# 2.3 Usuario administrador existe
$adminExists = & $MYSQL_EXE -u root $dbName -e "SELECT COUNT(*) FROM usuario WHERE email = 'admin@test.com';" 2>&1 | Select-Object -Skip 1
if ([int]$adminExists -gt 0) {
    Write-Host "✅ Usuario administrador existe" -ForegroundColor Green
} else {
    Write-Host "❌ Usuario administrador NO existe" -ForegroundColor Red
    $allTablesOk = $false
}

# 2.4 Charset correcto
$charset = & $MYSQL_EXE -u root -e "SELECT DEFAULT_CHARACTER_SET_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$dbName';" 2>&1 | Select-Object -Skip 1
if ($charset -eq "utf8mb4" -or $charset -eq "utf8") {
    Write-Host "✅ Charset correcto ($charset)" -ForegroundColor Green
} else {
    Write-Host "⚠️ Charset: $charset (se recomienda utf8mb4)" -ForegroundColor Yellow
}

return $allTablesOk
