#Requires -RunAsAdministrator

# ============================================================================
# COPIADOR DE ARCHIVOS - EGRESAPP2
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   COPIANDO ARCHIVOS DEL PROYECTO" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$SourcePath = $PSScriptRoot
$DestPath = "C:\laragon\www\EGRESAPP2"

# Verificar si Laragon existe
if (-not (Test-Path "C:\laragon\www")) {
    Write-Host "[X] No se encontro la carpeta www de Laragon" -ForegroundColor Red
    Write-Host "Asegurese de que Laragon este instalado en C:\laragon" -ForegroundColor Yellow
    exit 1
}

# Crear directorio destino
if (-not (Test-Path $DestPath)) {
    New-Item -ItemType Directory -Path $DestPath -Force | Out-Null
    Write-Host "[OK] Directorio creado: $DestPath" -ForegroundColor Green
}

Write-Host "Copiando archivos desde: $SourcePath" -ForegroundColor Cyan
Write-Host "Hacia: $DestPath" -ForegroundColor Cyan
Write-Host "Esto puede tardar unos momentos..." -ForegroundColor Yellow
Write-Host ""

try {
    # Copiar todo excepto carpetas de sistema/temporales
    $exclude = @('.git', '.vs', 'installers', 'backup', '*.log', '*.tmp')
    Copy-Item -Path "$SourcePath\*" -Destination $DestPath -Recurse -Force -Exclude $exclude
    
    Write-Host "[OK] Archivos copiados exitosamente" -ForegroundColor Green
    exit 0
}
catch {
    Write-Host "[X] Error al copiar archivos: $_" -ForegroundColor Red
    exit 1
}
