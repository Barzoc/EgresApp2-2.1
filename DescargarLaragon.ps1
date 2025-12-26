#Requires -RunAsAdministrator

# ============================================================================
# DESCARGADOR DE LARAGON - EGRESAPP2
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   DESCARGANDO LARAGON" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$InstallersDir = "$PSScriptRoot\installers"
$LaragonUrl = "https://github.com/leokhoa/laragon/releases/download/6.0.0/laragon-wamp.exe"
$LaragonFile = "$InstallersDir\laragon-wamp.exe"

# Crear carpeta installers si no existe
if (-not (Test-Path $InstallersDir)) {
    New-Item -ItemType Directory -Path $InstallersDir -Force | Out-Null
    Write-Host "[OK] Carpeta 'installers' creada" -ForegroundColor Green
}

# Verificar si ya existe
if (Test-Path $LaragonFile) {
    $size = (Get-Item $LaragonFile).Length / 1MB
    Write-Host "[OK] Laragon ya esta descargado ($([math]::Round($size, 2)) MB)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Puede ejecutar ahora: INSTALAR_SIMPLE.bat" -ForegroundColor Cyan
    Write-Host ""
    pause
    exit 0
}

# Descargar Laragon
Write-Host "Descargando Laragon (aproximadamente 180 MB)..." -ForegroundColor Cyan
Write-Host "Esto puede tardar varios minutos dependiendo de su conexion..." -ForegroundColor Yellow
Write-Host ""

try {
    # Descargar con barra de progreso
    $ProgressPreference = 'Continue'
    Invoke-WebRequest -Uri $LaragonUrl -OutFile $LaragonFile -UseBasicParsing
    
    if (Test-Path $LaragonFile) {
        $size = (Get-Item $LaragonFile).Length / 1MB
        Write-Host ""
        Write-Host "[OK] Laragon descargado exitosamente ($([math]::Round($size, 2)) MB)" -ForegroundColor Green
        Write-Host ""
        Write-Host "============================================================================" -ForegroundColor Cyan
        Write-Host "   DESCARGA COMPLETADA" -ForegroundColor Green
        Write-Host "============================================================================" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "SIGUIENTE PASO:" -ForegroundColor Yellow
        Write-Host "  1. Cierre esta ventana" -ForegroundColor White
        Write-Host "  2. Ejecute: INSTALAR_SIMPLE.bat (como administrador)" -ForegroundColor White
        Write-Host ""
    }
    else {
        Write-Host "[X] Error: el archivo no se descargo correctamente" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host ""
    Write-Host "[X] Error al descargar Laragon: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "SOLUCION ALTERNATIVA:" -ForegroundColor Yellow
    Write-Host "  1. Descargue manualmente desde: $LaragonUrl" -ForegroundColor Cyan
    Write-Host "  2. Guarde el archivo en: $InstallersDir" -ForegroundColor Cyan
    Write-Host "  3. Ejecute nuevamente: INSTALAR_SIMPLE.bat" -ForegroundColor Cyan
    Write-Host ""
    exit 1
}

Write-Host ""
pause
