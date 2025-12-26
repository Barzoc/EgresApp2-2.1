#Requires -RunAsAdministrator

# ============================================================================
# INSTALADOR DE LARAGON - EGRESAPP2 (CON AUTO-DESCARGA)
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   INSTALANDO LARAGON" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$InstallersDir = "$PSScriptRoot\installers"
$LaragonInstaller = "$InstallersDir\laragon-wamp.exe"
$LaragonPath = "C:\laragon"
$LaragonUrl = "https://github.com/leokhoa/laragon/releases/download/6.0.0/laragon-wamp.exe"

# Verificar si ya está instalado
if (Test-Path "$LaragonPath\laragon.exe") {
    Write-Host "[OK] Laragon ya esta instalado" -ForegroundColor Green
    exit 0
}

# Crear directorio installers si no existe
if (-not (Test-Path $InstallersDir)) {
    New-Item -ItemType Directory -Path $InstallersDir -Force | Out-Null
}

# Verificar si existe el instalador, si no, descargar
if (-not (Test-Path $LaragonInstaller)) {
    Write-Host "[INFO] El instalador de Laragon no se encontro localmente." -ForegroundColor Yellow
    Write-Host "       Descargando Laragon automaticamente (180 MB)..." -ForegroundColor Cyan
    Write-Host "       URL: $LaragonUrl" -ForegroundColor Gray
    Write-Host "       Esto puede tardar unos minutos..." -ForegroundColor Yellow
    
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri $LaragonUrl -OutFile $LaragonInstaller -UseBasicParsing
        
        if (Test-Path $LaragonInstaller) {
            Write-Host "[OK] Laragon descargado exitosamente" -ForegroundColor Green
        } else {
            Write-Host "[ERROR] No se pudo descargar Laragon." -ForegroundColor Red
            exit 1
        }
    }
    catch {
        Write-Host "[ERROR] Fallo la descarga: $_" -ForegroundColor Red
        Write-Host "Por favor descargue manualmente desde: $LaragonUrl" -ForegroundColor Cyan
        Write-Host "Y guardelo en: $LaragonInstaller" -ForegroundColor Cyan
        exit 1
    }
} else {
    Write-Host "[INFO] Usando instalador local: $LaragonInstaller" -ForegroundColor Cyan
}

# Instalar Laragon
Write-Host ""
Write-Host "Instalando Laragon..." -ForegroundColor Cyan
Write-Host "Esto puede tardar varios minutos..." -ForegroundColor Yellow
Write-Host ""

try {
    Start-Process -FilePath $LaragonInstaller -ArgumentList "/VERYSILENT /NORESTART" -Wait
    
    if (Test-Path "$LaragonPath\laragon.exe") {
        Write-Host "[OK] Laragon instalado exitosamente" -ForegroundColor Green
        
        # Iniciar Laragon
        Write-Host "Iniciando Laragon..." -ForegroundColor Cyan
        Start-Process "$LaragonPath\laragon.exe"
        Start-Sleep -Seconds 5
        
        Write-Host "[OK] Laragon iniciado" -ForegroundColor Green
        exit 0
    } else {
        Write-Host "[ERROR] Laragon no se instalo correctamente" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host "[ERROR] Error durante la instalacion: $_" -ForegroundColor Red
    exit 1
}
