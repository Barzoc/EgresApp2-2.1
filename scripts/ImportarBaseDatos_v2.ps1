#Requires -RunAsAdministrator

# ============================================================================
# IMPORTADOR OPTIMIZADO DE BASE DE DATOS - EGRESAPP2
# ============================================================================
# Versión mejorada con optimizaciones de velocidad y verificación
# ============================================================================

param(
    [string]$SQLFile = "$PSScriptRoot\..\db\gestion_egresados.sql",
    [string]$DBName = "gestion_egresados",
    [switch]$OptimizeForSpeed
)

Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     IMPORTADOR OPTIMIZADO DE BD - EGRESAPP2           ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Buscar MySQL
$mysqlBinPaths = @(
    "C:\laragon\bin\mysql\mysql-8.0.30\bin",
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin",
    "C:\xampp\mysql\bin"
)

$MYSQL_BIN = $null
foreach ($binPath in $mysqlBinPaths) {
    if (Test-Path "$binPath\mysql.exe") {
        $MYSQL_BIN = $binPath
        break
    }
}

if (-not $MYSQL_BIN) {
    Write-Host "✗ MySQL no encontrado" -ForegroundColor Red
    exit 1
}

$MYSQL_EXE = "$MYSQL_BIN\mysql.exe"

# Verificar archivo SQL
if (-not (Test-Path $SQLFile)) {
    Write-Host "✗ Archivo SQL no encontrado: $SQLFile" -ForegroundColor Red
    exit 1
}

$sqlSize = (Get-Item $SQLFile).Length / 1MB
Write-Host "Archivo SQL: $([math]::Round($sqlSize, 2)) MB" -ForegroundColor Cyan

# Estimar tiempo
$estimatedMinutes = [math]::Ceiling($sqlSize / 10)
Write-Host "Tiempo estimado: $estimatedMinutes minuto(s)" -ForegroundColor Yellow
Write-Host ""

# Esperar MySQL
Write-Host "Esperando a que MySQL esté listo..." -ForegroundColor Cyan
$maxAttempts = 30
$attempt = 0
$mysqlReady = $false

while ($attempt -lt $maxAttempts -and -not $mysqlReady) {
    try {
        $testResult = & $MYSQL_EXE -u root -e "SELECT 1;" 2>&1
        if ($LASTEXITCODE -eq 0) {
            $mysqlReady = $true
        }
    } catch {}
    
    if (-not $mysqlReady) {
        Start-Sleep -Seconds 2
        $attempt++
        Write-Host "." -NoNewline -ForegroundColor Yellow
    }
}

Write-Host ""

if (-not $mysqlReady) {
    Write-Host "✗ MySQL no respondió" -ForegroundColor Red
    exit 1
}

Write-Host "✓ MySQL está listo" -ForegroundColor Green
Write-Host ""

# Crear base de datos
Write-Host "Creando base de datos '$DBName'..." -ForegroundColor Cyan
& $MYSQL_EXE -u root -e "CREATE DATABASE IF NOT EXISTS ``$DBName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1 | Out-Null

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Base de datos creada/verificada" -ForegroundColor Green
} else {
    Write-Host "✗ Error al crear base de datos" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Aplicar optimizaciones
if ($OptimizeForSpeed) {
    Write-Host "⚡ Aplicando optimizaciones de velocidad..." -ForegroundColor Yellow
    
    & $MYSQL_EXE -u root $DBName -e @"
SET autocommit=0;
SET unique_checks=0;
SET foreign_key_checks=0;
"@ 2>&1 | Out-Null
    
    Write-Host "✓ Optimizaciones aplicadas" -ForegroundColor Green
}

# Importar con barra de progreso
Write-Host ""
Write-Host "Importando datos..." -ForegroundColor Cyan
Write-Host "Por favor espere, esto puede tomar varios minutos..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

try {
    & $MYSQL_EXE -u root $DBName < $SQLFile 2>&1 | Out-Null
    
    if ($LASTEXITCODE -eq 0) {
        $endTime = Get-Date
        $duration = $endTime - $startTime
        
        Write-Host "✓ Base de datos importada exitosamente" -ForegroundColor Green
        Write-Host "  Tiempo transcurrido: $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Cyan
    } else {
        Write-Host "✗ Error al importar base de datos" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Error: $_" -ForegroundColor Red
    exit 1
}

# Restaurar configuraciones
if ($OptimizeForSpeed) {
    Write-Host ""
    Write-Host "Restaurando configuraciones..." -ForegroundColor Cyan
    
    & $MYSQL_EXE -u root $DBName -e @"
SET autocommit=1;
SET unique_checks=1;
SET foreign_key_checks=1;
"@ 2>&1 | Out-Null
}

# Verificar importación
Write-Host ""
Write-Host "Verificando importación..." -ForegroundColor Cyan

$tableCount = & $MYSQL_EXE -u root $DBName -e "SHOW TABLES;" 2>&1 | Measure-Object -Line
$tables = & $MYSQL_EXE -u root $DBName -e "SHOW TABLES;" 2>&1 | Select-Object -Skip 1

Write-Host "✓ Tablas importadas: $($tableCount.Lines - 1)" -ForegroundColor Green

# Verificar tablas críticas
$criticalTables = @("egresado", "titulo", "tituloegresado", "usuario")
foreach ($table in $criticalTables) {
    if ($tables -contains $table) {
        $count = & $MYSQL_EXE -u root $DBName -e "SELECT COUNT(*) FROM $table;" 2>&1 | Select-Object -Skip 1
        Write-Host "  ✓ $table : $count registros" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $table : FALTANTE" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         ✅ IMPORTACIÓN COMPLETADA ✅                   ║" -ForegroundColor Green
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

exit 0
