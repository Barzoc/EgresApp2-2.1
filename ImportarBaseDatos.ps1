#Requires -RunAsAdministrator

# ============================================================================
# IMPORTADOR DE BASE DE DATOS - EGRESAPP2
# ============================================================================

param(
    [string]$SQLFile = "$PSScriptRoot\db\gestion_egresados.sql",
    [string]$DBName = "gestion_egresados",
    [string]$MySQLPath = "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe"
)

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   IMPORTANDO BASE DE DATOS" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

# Buscar MySQL en ubicaciones comunes
$mysqlLocations = @(
    "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-5.7.24\bin\mysql.exe",
    "C:\xampp\mysql\bin\mysql.exe",
    "C:\wamp\bin\mysql\mysql8.0.30\bin\mysql.exe"
)

$MySQLPath = $null
foreach ($location in $mysqlLocations) {
    if (Test-Path $location) {
        $MySQLPath = $location
        break
    }
}

if (-not $MySQLPath) {
    Write-Host "[X] No se encontro MySQL. Asegurese de que Laragon este instalado." -ForegroundColor Red
    exit 1
}

Write-Host "[OK] MySQL encontrado en: $MySQLPath" -ForegroundColor Green

# Verificar archivo SQL
if (-not (Test-Path $SQLFile)) {
    Write-Host "[X] No se encontro el archivo SQL: $SQLFile" -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Archivo SQL encontrado: $SQLFile" -ForegroundColor Green
Write-Host ""

# Esperar a que MySQL esté listo  
Write-Host "Esperando a que MySQL este listo..." -ForegroundColor Yellow
$maxAttempts = 30
$attempt = 0
$mysqlReady = $false

while ($attempt -lt $maxAttempts -and -not $mysqlReady) {
    try {
        $testResult = & $MySQLPath -u root -e "SELECT 1;" 2>&1
        if ($LASTEXITCODE -eq 0) {
            $mysqlReady = $true
        }
    }
    catch {
        # MySQL no esta listo aun
    }
    
    if (-not $mysqlReady) {
        Start-Sleep -Seconds 2
        $attempt++
        Write-Host "." -NoNewline -ForegroundColor Yellow
    }
}

Write-Host ""

if (-not $mysqlReady) {
    Write-Host "[X] MySQL no respondio despues de $maxAttempts intentos" -ForegroundColor Red
    Write-Host "Por favor, inicie MySQL manualmente desde Laragon" -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] MySQL esta listo" -ForegroundColor Green
Write-Host ""

# Crear base de datos
Write-Host "Creando base de datos '$DBName'..." -ForegroundColor Yellow
try {
    & $MySQLPath -u root -e "CREATE DATABASE IF NOT EXISTS ``$DBName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    Write-Host "[OK] Base de datos creada/verificada" -ForegroundColor Green
}
catch {
    Write-Host "[X] Error al crear base de datos: $_" -ForegroundColor Red
    exit 1
}

# Importar SQL
Write-Host ""
Write-Host "Importando datos..." -ForegroundColor Yellow
Write-Host "Esto puede tomar varios minutos dependiendo del tamano de la base de datos..." -ForegroundColor Cyan
Write-Host ""

try {
    # Usar cmd /c para permitir la redirección de entrada <
    $importCmd = "cmd /c `"`"$MySQLPath`" -u root $DBName < `"$SQLFile`"`""
    Write-Host "Ejecutando: $importCmd" -ForegroundColor Gray
    Invoke-Expression $importCmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Base de datos importada correctamente" -ForegroundColor Green
        
        # Verificar importacion  
        Write-Host ""
        Write-Host "Verificando importacion..." -ForegroundColor Yellow
        $tableCount = & $MySQLPath -u root -D $DBName -e "SHOW TABLES;" 2>&1
        
        if ($tableCount) {
            Write-Host "[OK] Importacion verificada exitosamente" -ForegroundColor Green
            Write-Host ""
            Write-Host "Tablas importadas:" -ForegroundColor Cyan
            Write-Host $tableCount
        }
        
        exit 0
    }
    else {
        Write-Host "[X] Error al importar base de datos" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host "[X] Error al importar: $_" -ForegroundColor Red
    exit 1
}
