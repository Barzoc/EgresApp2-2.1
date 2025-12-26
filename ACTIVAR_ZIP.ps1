# ACTIVAR_ZIP.ps1 - Habilitar extension=zip en php.ini ACTIVO

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   HABILITANDO EXTENSION ZIP PHP LARAGON (SMART)" -ForegroundColor Cyan
Write-Host "================================================"
Write-Host ""

# 1. Detectar qué php.ini se está usando realmente
$phpIniInfo = & php --ini
$loadedIniPath = ""

foreach ($line in $phpIniInfo) {
    if ($line -match "Loaded Configuration File:\s*(.*)$") {
        $loadedIniPath = $matches[1].Trim()
        break
    }
}

if ([string]::IsNullOrEmpty($loadedIniPath) -or $loadedIniPath -eq "(none)") {
    Write-Host "[ALERTA] No se detectó un archivo php.ini cargado automáticamente." -ForegroundColor Yellow
    Write-Host "Intentando buscar en carpetas de Laragon..." -ForegroundColor Yellow
    $loadedIniPath = ""
}
else {
    Write-Host "Detectado php.ini ACTIVO: $loadedIniPath" -ForegroundColor Green
}

# Lista de archivos a procesar (el activo + todos los encontrados en laragon/bin/php)
$filesToProcess = @()
if (![string]::IsNullOrEmpty($loadedIniPath) -and (Test-Path $loadedIniPath)) {
    $filesToProcess += $loadedIniPath
}

$laragonPhpPath = "C:\laragon\bin\php"
if (Test-Path $laragonPhpPath) {
    $phpVersions = Get-ChildItem -Path $laragonPhpPath -Directory
    foreach ($version in $phpVersions) {
        $iniPath = Join-Path $version.FullName "php.ini"
        if (Test-Path $iniPath) {
            if ($filesToProcess -notcontains $iniPath) {
                $filesToProcess += $iniPath
            }
        }
    }
}

# Procesar archivos
foreach ($iniPath in $filesToProcess) {
    Write-Host "Procesando: $iniPath" -ForegroundColor Yellow
    
    $content = Get-Content $iniPath
    $newContent = @()
    $modified = $false
    
    foreach ($line in $content) {
        # Descomentar extension=zip (quita ; precedentes)
        if ($line -match "^\s*;?\s*extension\s*=\s*zip\s*$") {
            $newContent += "extension=zip"
            if (!($line.Trim().StartsWith("extension=zip"))) {
                $modified = $true
                Write-Host "   -> [ACTIVADO] extension=zip" -ForegroundColor Green
            }
            else {
                Write-Host "   -> [YA ACTIVO] extension=zip" -ForegroundColor Gray
            }
        }
        else {
            $newContent += $line
        }
    }
    
    if ($modified) {
        try {
            Set-Content -Path $iniPath -Value $newContent -Encoding UTF8
            Write-Host "   [GUARDADO] php.ini actualizado." -ForegroundColor Green
        }
        catch {
            Write-Host "   [ERROR] No se pudo guardar. Ejecuta como Administrador." -ForegroundColor Red
        }
    }
}

# 2. Verificar si se cargó el modulo
Write-Host ""
Write-Host "Reiniciando servicio Apache para aplicar cambios..." -ForegroundColor Cyan

try {
    # Intentar reinicio suave primero
    $service = Get-Service -Name "Apache" -ErrorAction SilentlyContinue
    if ($service) {
        Restart-Service -Name "Apache" -Force -ErrorAction Stop
        Write-Host "Servicio Apache reiniciado correctamente." -ForegroundColor Green
    }
    else {
        # Si no lo encuentra por nombre 'Apache' (a veces es Apache2.4)
        $service2 = Get-Service -Name "Apache2.4" -ErrorAction SilentlyContinue
        if ($service2) {
            Restart-Service -Name "Apache2.4" -Force -ErrorAction Stop
            Write-Host "Servicio Apache2.4 reiniciado correctamente." -ForegroundColor Green
        }
        else {
            Write-Host "[ALERTA] No se encontró servicio Apache/Apache2.4. Debes reiniciar Laragon MANUALMENTE." -ForegroundColor Yellow
        }
    }
}
catch {
    Write-Host "[ERROR] Falló el reinicio automático del servicio. Detalle: $_" -ForegroundColor Red
    Write-Host "POR FAVOR REINICIA LARAGON MANUALMENTE (Click 'Stop' -> 'Start All')" -ForegroundColor Magenta
}

Start-Sleep -Seconds 3

Write-Host "Verificando extensión 'zip' (CLI)..." -ForegroundColor Cyan
$modules = & php -m

if ($modules -contains "zip") {
    Write-Host "EXITO: La extensión 'zip' está CORRECTAMENTE cargada." -ForegroundColor Green
}
else {
    Write-Host "ALERTA: La extensión 'zip' NO aparece cargada en CLI." -ForegroundColor Red
    Write-Host "Posibles causas:"
    Write-Host "1. Falta reiniciar Laragon manualmente."
    Write-Host "2. El archivo DLL 'php_zip.dll' no existe en /ext."
    Write-Host "3. Estás editando un php.ini diferente al que usa el servidor web."
}

Write-Host ""
Write-Host "Proceso completado." -ForegroundColor Cyan
