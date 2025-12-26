#Requires -RunAsAdministrator

# ============================================================================
# INSTALADOR DE DEPENDENCIAS - EGRESAPP2 (CON AUTO-DESCARGA)
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   INSTALANDO DEPENDENCIAS" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$ProjectRoot = $PSScriptRoot
$InstallersDir = "$ProjectRoot\installers"

# Crear directorio installers si no existe
if (-not (Test-Path $InstallersDir)) {
    New-Item -ItemType Directory -Path $InstallersDir -Force | Out-Null
}

# Configuración de URLs de descarga (Fallbacks)
$UrlTesseract = "https://github.com/UB-Mannheim/tesseract/releases/download/v5.3.3/tesseract-ocr-w64-setup-v5.3.3.20231005.exe"
$UrlImageMagick = "https://imagemagick.org/archive/binaries/ImageMagick-7.1.1-29-Q16-HDRI-x64-dll.exe"
$UrlLibreOffice = "https://downloadarchive.documentfoundation.org/libreoffice/old/7.6.4.1/win/x86_64/LibreOffice_7.6.4.1_Win_x86-64.msi"

# Función de descarga helper
function Download-Installer {
    param($Url, $DestPath, $Name)
    Write-Host "[INFO] Descargando $Name..." -ForegroundColor Cyan
    Write-Host "       URL: $Url" -ForegroundColor Gray
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri $Url -OutFile $DestPath -UseBasicParsing
        if (Test-Path $DestPath) {
            Write-Host "[OK] Descarga completada" -ForegroundColor Green
            return $true
        }
    } catch {
        Write-Host "[ERROR] Fallo la descarga de ${Name}: $_" -ForegroundColor Red
    }
    return $false
}

# ============================================================================
# 1. COMPOSER
# ============================================================================

Write-Host "1. Instalando Composer..." -ForegroundColor Cyan

try {
    $composerTest = composer --version 2>&1
    if ($composerTest -match "Composer") {
        Write-Host "[OK] Composer ya esta instalado" -ForegroundColor Green
    }
}
catch {
    Write-Host "[!] Composer no encontrado, instalando..." -ForegroundColor Yellow
    
    $composerInstaller = "$InstallersDir\composer-setup.exe"
    $UrlComposer = "https://getcomposer.org/Composer-Setup.exe"
    
    # Descargar si no existe
    if (-not (Test-Path $composerInstaller)) {
        Write-Host "[INFO] Instalador de Composer no encontrado." -ForegroundColor Yellow
        if (Download-Installer $UrlComposer $composerInstaller "Composer") {
            # Continuar
        } else {
             Write-Host "[X] No se pudo descargar Composer" -ForegroundColor Red
        }
    }
    
    # Instalar Composer
    if (Test-Path $composerInstaller) {
        Write-Host "[INFO] Ejecutando instalador de Composer..." -ForegroundColor Cyan
        try {
            # Instalación silenciosa de Composer
            Start-Process -FilePath $composerInstaller -ArgumentList "/VERYSILENT /ALLUSERS" -Wait
            
            # Agregar a PATH (intento manual por si acaso)
            $composerBatDir = "$env:ProgramFiles\Composer"
            $currentPath = [System.Environment]::GetEnvironmentVariable('Path', 'Machine')
            if ($currentPath -notlike "*Composer*") {
                [System.Environment]::SetEnvironmentVariable('Path', "$currentPath;$composerBatDir", 'Machine')
            }
            Write-Host "[OK] Composer instalado" -ForegroundColor Green
        } catch {
             Write-Host "[!] Error al instalar Composer: $_" -ForegroundColor Red
        }
    }
}

# Instalar dependencias PHP
Write-Host ""
Write-Host "Instalando dependencias PHP de Composer..." -ForegroundColor Cyan

if (Test-Path "$ProjectRoot\composer.json") {
    Set-Location $ProjectRoot
    
    # Fix para error de "dubious ownership" en Git
    try {
        git config --global --add safe.directory $ProjectRoot
        git config --global --add safe.directory "D:/EGRESAPP2"
    } catch {
        # Ignorar
    }

    # Intentar usar composer (puede requerir reiniciar shell, así que usamos path completo si es necesario)
    try {
        composer install --no-interaction --prefer-dist
    } catch {
        Write-Host "[!] No se pudo ejecutar 'composer install'. Puede que necesite reiniciar para actualizar el PATH." -ForegroundColor Yellow
        Write-Host "    Intente ejecutar manualmente: composer install" -ForegroundColor Yellow
    }
}

# ============================================================================
# 2. TESSERACT OCR
# ============================================================================

Write-Host ""
Write-Host "2. Verificando Tesseract OCR..." -ForegroundColor Cyan

try {
    $tesseractTest = tesseract --version 2>&1
    if ($tesseractTest -match "tesseract") {
        Write-Host "[OK] Tesseract ya esta instalado" -ForegroundColor Green
    }
}
catch {
    # Buscar instalador local
    $installerPath = "$InstallersDir\tesseract-installer.exe"
    
    if (-not (Test-Path $installerPath)) {
        Write-Host "[INFO] Instalador local no encontrado." -ForegroundColor Yellow
        if (Download-Installer $UrlTesseract $installerPath "Tesseract OCR") {
            # Continuar a instalación
        } else {
            Write-Host "[X] No se pudo instalar Tesseract (Fallo descarga)" -ForegroundColor Red
        }
    }
    
    if (Test-Path $installerPath) {
        Write-Host "[INFO] Instalando Tesseract..." -ForegroundColor Cyan
        try {
            Start-Process -FilePath $installerPath -ArgumentList "/S" -Wait
            Write-Host "[OK] Tesseract instalado" -ForegroundColor Green
        } catch {
            Write-Host "[!] Error al instalar Tesseract: $_" -ForegroundColor Red
        }
    }
}

# ============================================================================
# 3. IMAGEMAGICK
# ============================================================================

Write-Host ""
Write-Host "3. Verificando ImageMagick..." -ForegroundColor Cyan

try {
    $imageMagickTest = convert --version 2>&1
    if ($imageMagickTest -match "ImageMagick") {
        Write-Host "[OK] ImageMagick ya esta instalado" -ForegroundColor Green
    }
}
catch {
    # Buscar instalador local
    $installerPath = "$InstallersDir\ImageMagick-7.1.2-8-Q16-HDRI-x64-dll.exe"
    
    if (-not (Test-Path $installerPath)) {
        Write-Host "[INFO] Instalador local no encontrado." -ForegroundColor Yellow
        if (Download-Installer $UrlImageMagick $installerPath "ImageMagick") {
            # Continuar
        } else {
            Write-Host "[X] No se pudo instalar ImageMagick (Fallo descarga)" -ForegroundColor Red
        }
    }
    
    if (Test-Path $installerPath) {
        Write-Host "[INFO] Instalando ImageMagick..." -ForegroundColor Cyan
        try {
            Start-Process -FilePath $installerPath -ArgumentList "/VERYSILENT /NORESTART" -Wait
            Write-Host "[OK] ImageMagick instalado" -ForegroundColor Green
        } catch {
            Write-Host "[!] Error al instalar ImageMagick: $_" -ForegroundColor Red
        }
    }
}

# ============================================================================
# 4. LIBREOFFICE
# ============================================================================

Write-Host ""
Write-Host "4. Verificando LibreOffice..." -ForegroundColor Cyan

$libreOfficePath = "C:\Program Files\LibreOffice\program\soffice.exe"
if (Test-Path $libreOfficePath) {
    Write-Host "[OK] LibreOffice esta instalado" -ForegroundColor Green
}
else {
    # Buscar instalador local (cualquier versión)
    $installerPattern = "$InstallersDir\LibreOffice*.msi"
    $installerFile = Get-ChildItem -Path $installerPattern | Select-Object -First 1
    $installerPath = ""

    if ($installerFile) {
        $installerPath = $installerFile.FullName
    } else {
        Write-Host "[INFO] Instalador local no encontrado." -ForegroundColor Yellow
        $downloadPath = "$InstallersDir\LibreOffice_Installer.msi"
        if (Download-Installer $UrlLibreOffice $downloadPath "LibreOffice") {
            $installerPath = $downloadPath
        } else {
            Write-Host "[X] No se pudo instalar LibreOffice (Fallo descarga)" -ForegroundColor Red
        }
    }
    
    if ($installerPath -and (Test-Path $installerPath)) {
        Write-Host "[INFO] Instalando LibreOffice..." -ForegroundColor Cyan
        try {
            Start-Process -FilePath "msiexec.exe" -ArgumentList "/i `"$installerPath`" /quiet /norestart" -Wait
            
            if (Test-Path $libreOfficePath) {
                Write-Host "[OK] LibreOffice instalado correctamente" -ForegroundColor Green
            } else {
                Write-Host "[!] La instalacion parece haber fallado (verifique manualmente)" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "[!] Error al ejecutar instalador: $_" -ForegroundColor Red
        }
    }
}

# ============================================================================
# 5. CREAR CARPETAS NECESARIAS
# ============================================================================

Write-Host ""
Write-Host "5. Creando carpetas necesarias..." -ForegroundColor Cyan

$folders = @(
    "$ProjectRoot\certificados",
    "$ProjectRoot\temp",
    "$ProjectRoot\tmp",
    "$ProjectRoot\assets\expedientes\expedientes_subidos"
)

foreach ($folder in $folders) {
    if (-not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder -Force | Out-Null
        Write-Host "[OK] Creada: $(Split-Path $folder -Leaf)" -ForegroundColor Green
    }
}

# ============================================================================
# 6. HABILITAR EXTENSIONES PHP
# ============================================================================

Write-Host ""
Write-Host "6. Verificando extensiones PHP..." -ForegroundColor Cyan

$phpIniPaths = @(
    "C:\laragon\bin\php\php-8.0.30\php.ini",
    "C:\laragon\bin\php\php-8.1.0\php.ini",
    "C:\xampp\php\php.ini"
)

$phpIniPath = $null
foreach ($path in $phpIniPaths) {
    if (Test-Path $path) {
        $phpIniPath = $path
        break
    }
}

if ($phpIniPath) {
    Write-Host "[OK] php.ini encontrado en: $phpIniPath" -ForegroundColor Green
    # Aquí se podría agregar lógica para descomentar extensiones automáticamente si se desea
}
else {
    Write-Host "[!] No se encontro php.ini" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   INSTALACION DE DEPENDENCIAS COMPLETADA" -ForegroundColor Green
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""
exit 0
