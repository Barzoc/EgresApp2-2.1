#Requires -RunAsAdministrator

# ============================================================================
# PRE-FLIGHT CHECKS - EGRESAPP2
# ============================================================================
# Verifica que el entorno cumple con todos los requisitos antes de instalar
# ============================================================================

param(
    [string]$ProjectRoot = "$PSScriptRoot\..",
    [string]$MinPHPVersion = "8.0.0"
)

$script:ChecksPassed = 0
$script:ChecksFailed = 0

function Write-CheckResult {
    param(
        [string]$Message,
        [bool]$Success
    )
    
    if ($Success) {
        Write-Host "✓ $Message" -ForegroundColor Green
        $script:ChecksPassed++
    } else {
        Write-Host "✗ $Message" -ForegroundColor Red
        $script:ChecksFailed++
    }
}

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         PRE-FLIGHT CHECKS - EGRESAPP2                 ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# CHECK 1: PHP Environment
# ============================================================================
Write-Host "CHECK 1: Entorno PHP" -ForegroundColor Magenta
Write-Host "-----------------------------------------------------------" -ForegroundColor Gray

$phpPaths = @(
    "C:\laragon\bin\php\php-8.0.30\php.exe",
    "C:\laragon\bin\php\php-8.1.0\php.exe",
    "C:\laragon\bin\php\php-8.2.0\php.exe",
    "C:\xampp\php\php.exe"
)

$phpExe = $null
foreach ($path in $phpPaths) {
    if (Test-Path $path) {
        $phpExe = $path
        break
    }
}

if ($phpExe) {
    Write-CheckResult "PHP encontrado en: $phpExe" $true
    
    # Verificar versión
    $versionOutput = & $phpExe --version 2>&1
    if ($versionOutput -match "PHP (\d+\.\d+\.\d+)") {
        $currentVersion = [version]$matches[1]
        $minVersion = [version]$MinPHPVersion
        
        if ($currentVersion -ge $minVersion) {
            Write-CheckResult "Versión PHP $currentVersion (>= $MinPHPVersion)" $true
        } else {
            Write-CheckResult "Versión PHP $currentVersion insuficiente (requiere $MinPHPVersion+)" $false
        }
    }
    
    # Verificar extensiones críticas
    $modules = & $phpExe -m 2>&1
    $requiredExtensions = @("mysqli", "pdo_mysql", "zip", "gd", "curl", "mbstring", "fileinfo")
    
    foreach ($ext in $requiredExtensions) {
        $found = $modules -match "^$ext$"
        Write-CheckResult "Extensión PHP: $ext" $found
    }
} else {
    Write-CheckResult "PHP instalado" $false
}

Write-Host ""

# ============================================================================
# CHECK 2: Database Availability
# ============================================================================
Write-Host "CHECK 2: Disponibilidad de Base de Datos" -ForegroundColor Magenta
Write-Host "-----------------------------------------------------------" -ForegroundColor Gray

$mysqlLocations = @(
    "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe",
    "C:\xampp\mysql\bin\mysql.exe"
)

$mysqlExe = $null
foreach ($location in $mysqlLocations) {
    if (Test-Path $location) {
        $mysqlExe = $location
        break
    }
}

if ($mysqlExe) {
    Write-CheckResult "MySQL cliente encontrado" $true
    
    # Verificar que MySQL esté corriendo
    $mysqld = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
    if ($mysqld) {
        Write-CheckResult "Servicio MySQL corriendo (PID: $($mysqld.Id))" $true
        
        # Intentar conexión
        $testQuery = & $mysqlExe -u root -e "SELECT 1;" 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-CheckResult "Conexión a MySQL exitosa" $true
        } else {
            Write-CheckResult "Conexión a MySQL fallida" $false
        }
    } else {
        Write-CheckResult "Servicio MySQL corriendo" $false
    }
} else {
    Write-CheckResult "MySQL instalado" $false
}

# Verificar puerto 3306
$port3306 = netstat -ano | Select-String ":3306 " | Select-String "LISTENING"
Write-CheckResult "Puerto 3306 disponible/escuchando" ($null -ne $port3306)

Write-Host ""

# ============================================================================
# CHECK 3: Directory Permissions
# ============================================================================
Write-Host "CHECK 3: Permisos de Directorios" -ForegroundColor Magenta
Write-Host "-----------------------------------------------------------" -ForegroundColor Gray

$criticalDirs = @(
    "$ProjectRoot\certificados",
    "$ProjectRoot\temp",
    "$ProjectRoot\tmp",
    "$ProjectRoot\assets\expedientes\expedientes_subidos"
)

foreach ($dir in $criticalDirs) {
    # Crear directorio si no existe
    if (-not (Test-Path $dir)) {
        try {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-CheckResult "Directorio creado: $(Split-Path $dir -Leaf)" $true
        } catch {
            Write-CheckResult "No se pudo crear: $(Split-Path $dir -Leaf)" $false
            continue
        }
    }
    
    # Probar permisos de escritura
    $testFile = "$dir\.write_test_$(Get-Random).tmp"
    try {
        "test" | Out-File -FilePath $testFile -Force
        Remove-Item $testFile -Force
        Write-CheckResult "Permisos de escritura: $(Split-Path $dir -Leaf)" $true
    } catch {
        Write-CheckResult "Permisos de escritura: $(Split-Path $dir -Leaf)" $false
    }
}

Write-Host ""

# ============================================================================
# CHECK 4: External Tools
# ============================================================================
Write-Host "CHECK 4: Herramientas Externas" -ForegroundColor Magenta
Write-Host "-----------------------------------------------------------" -ForegroundColor Gray

# Tesseract OCR
try {
    $tesseract = & tesseract --version 2>&1
    if ($tesseract -match "tesseract") {
        Write-CheckResult "Tesseract OCR instalado" $true
        
        # Verificar idioma español
        $langs = & tesseract --list-langs 2>&1
        $spaFound = $langs -match "spa"
        Write-CheckResult "Tesseract: idioma español (spa)" $spaFound
    } else {
        Write-CheckResult "Tesseract OCR instalado" $false
    }
} catch {
    Write-CheckResult "Tesseract OCR instalado" $false
}

# ImageMagick
try {
    $convert = & convert --version 2>&1
    Write-CheckResult "ImageMagick instalado" ($convert -match "ImageMagick")
} catch {
    Write-CheckResult "ImageMagick instalado" $false
}

# LibreOffice
$soffice = "C:\Program Files\LibreOffice\program\soffice.exe"
Write-CheckResult "LibreOffice instalado" (Test-Path $soffice)

# Composer
try {
    $composer = & composer --version 2>&1
    Write-CheckResult "Composer instalado" ($composer -match "Composer")
} catch {
    Write-CheckResult "Composer instalado" $false
}

Write-Host ""

# ============================================================================
# CHECK 5: Network Ports
# ============================================================================
Write-Host "CHECK 5: Puertos de Red" -ForegroundColor Magenta
Write-Host "-----------------------------------------------------------" -ForegroundColor Gray

$port80 = netstat -ano | Select-String ":80 " | Select-String "LISTENING"
Write-CheckResult "Puerto 80 disponible" ($null -ne $port80)

$port443 = netstat -ano | Select-String ":443 " | Select-String "LISTENING"
if ($port443) {
    Write-Host "ℹ Puerto 443 en uso (HTTPS configurado)" -ForegroundColor Cyan
}

Write-Host ""

# ============================================================================
# RESUMEN
# ============================================================================
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                   RESUMEN                             ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "Checks pasados:  " -NoNewline -ForegroundColor White
Write-Host "$ChecksPassed" -ForegroundColor Green
Write-Host "Checks fallidos: " -NoNewline -ForegroundColor White
Write-Host "$ChecksFailed" -ForegroundColor $(if ($ChecksFailed -eq 0) {"Green"} else {"Red"})
Write-Host ""

if ($ChecksFailed -eq 0) {
    Write-Host "✅ TODOS LOS CHECKS PASARON" -ForegroundColor Green
    Write-Host "El entorno está listo para la instalación." -ForegroundColor White
    exit 0
} elseif ($ChecksFailed -le 3) {
    Write-Host "⚠️ ALGUNOS CHECKS FALLARON" -ForegroundColor Yellow
    Write-Host "Se recomienda corregir los errores antes de continuar." -ForegroundColor White
    exit 1
} else {
    Write-Host "❌ MÚLTIPLES CHECKS FALLARON" -ForegroundColor Red
    Write-Host "No se puede continuar con la instalación." -ForegroundColor White
    exit 2
}
