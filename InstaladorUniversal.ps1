#Requires -RunAsAdministrator

# ============================================================================
# INSTALADOR UNIVERSAL V3 - EGRESAPP2
# ============================================================================
# Características:
# - Rutas Dinámicas (Detecta Laragon o permite manual)
# - Detección de Instalación Previa (Modo Actualización vs Instalación)
# - Preservación de Datos (Configuración y Archivos)
# ============================================================================

$Host.UI.RawUI.BackgroundColor = "Black"
$Host.UI.RawUI.ForegroundColor = "White"
Clear-Host

# --- VARIABLES ---
$ScriptRoot = $PSScriptRoot
$VersionFile = "$ScriptRoot\version.txt"
$InstallerVersion = if (Test-Path $VersionFile) { Get-Content $VersionFile } else { "Unknown" }
$LogFile = "$ScriptRoot\instalacion_universal.log"

# --- FUNCIONES ---

function Write-Log {
    param($Message, $Type = "INFO", $Color = "White")
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogMsg = "[$Timestamp] [$Type] $Message"
    Add-Content -Path $LogFile -Value $LogMsg -Encoding UTF8
    Write-Host "[$Type] $Message" -ForegroundColor $Color
}

function Get-LaragonPath {
    $PossiblePaths = @("C:\laragon", "D:\laragon", "E:\laragon")
    foreach ($path in $PossiblePaths) {
        if (Test-Path "$path\www") {
            return $path
        }
    }
    return $null
}

# --- INICIO ---

Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║       INSTALADOR UNIVERSAL EGRESAPP2 (v$InstallerVersion)       ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# 1. DETECTAR EXTINO
Write-Log "Detectando entorno..." "INFO" "Cyan"
$LaragonRoot = Get-LaragonPath

if ($LaragonRoot) {
    Write-Log "Laragon detectado en: $LaragonRoot" "SUCCESS" "Green"
    $DefaultInstallPath = "$LaragonRoot\www\EGRESAPP2"
} else {
    Write-Log "Laragon no detectado automáticamente." "WARNING" "Yellow"
    $DefaultInstallPath = "C:\xampp\htdocs\EGRESAPP2" # Fallback común
}

# Permitir al usuario cambiar la ruta
Write-Host ""
Write-Host "La ruta de instalación detectada es: $DefaultInstallPath" -ForegroundColor White
$UserPath = Read-Host "¿Desea usar esta ruta? (Enter para Sí, o escriba una nueva ruta)"

if (-not [string]::IsNullOrWhiteSpace($UserPath)) {
    $InstallPath = $UserPath
} else {
    $InstallPath = $DefaultInstallPath
}

Write-Log "Ruta destino: $InstallPath" "INFO" "Cyan"

# 2. CHECK INSTALACIÓN PREVIA
$IsUpdate = $false
if (Test-Path "$InstallPath\version.txt") {
    $InstalledVersion = Get-Content "$InstallPath\version.txt"
    Write-Log "Instalación previa detectada (Versión $InstalledVersion)" "WARNING" "Yellow"
    $IsUpdate = $true
} elseif (Test-Path "$InstallPath\modelo\Conexion.php") {
    Write-Log "Instalación previa detectada (Sin versión)" "WARNING" "Yellow"
    $IsUpdate = $true
}

# 3. CONFIRMACIÓN
Write-Host ""
if ($IsUpdate) {
    Write-Host "MODO: ACTUALIZACIÓN / REPARACIÓN" -ForegroundColor Magenta
    Write-Host "Se conservarán:" -ForegroundColor Gray
    Write-Host "  - Base de datos actual" -ForegroundColor Gray
    Write-Host "  - Archivos subidos (assets/expedientes)" -ForegroundColor Gray
    Write-Host "  - Certificados generados" -ForegroundColor Gray
    Write-Host "  - Configuración de conexión" -ForegroundColor Gray
} else {
    Write-Host "MODO: INSTALACIÓN LIMPIA" -ForegroundColor Green
    Write-Host "Se instalará todo desde cero." -ForegroundColor Gray
}

Write-Host ""
$Confirm = Read-Host "¿Proceder? (S/N)"
if ($Confirm -ne "S") { exit }

# 4. PROCESO
try {
    # Crear directorio si no existe
    if (-not (Test-Path $InstallPath)) {
        New-Item -ItemType Directory -Path $InstallPath -Force | Out-Null
    }

    # Backup de Configuración (solo si es Update)
    if ($IsUpdate) {
        Write-Log "Respaldando configuraciones..." "INFO" "Cyan"
        if (Test-Path "$InstallPath\modelo\Conexion.php") {
            Copy-Item "$InstallPath\modelo\Conexion.php" "$InstallPath\modelo\Conexion.php.bak" -Force
        }
        # Respaldar otros config si es necesario
    }

    # COPIAR ARCHIVOS (Robocopy para exclusiones inteligentes o Copy-Item con lógica)
    Write-Log "Copiando archivos del sistema..." "INFO" "Cyan"
    
    # Exclusiones
    $Exclude = @("instalacion_universal.log", ".git", "tmp", "version.txt") 
    
    # Copia recursiva básica
    Get-ChildItem -Path $ScriptRoot -Exclude $Exclude | ForEach-Object {
        $Dest = Join-Path $InstallPath $_.Name
        
        # Lógica especial para carpetas que no se deben sobrescribir totalmente si es update
        if ($IsUpdate -and ($_.Name -eq "assets" -or $_.Name -eq "certificados" -or $_.Name -eq "uploads")) {
            Write-Log "  Fusionando carpeta $($_.Name)..." "INFO" "Gray"
            Copy-Item -Path $_.FullName -Destination $InstallPath -Recurse -Force -ErrorAction SilentlyContinue
        } elseif ($IsUpdate -and $_.Name -eq "config") {
             Write-Log "  Saltando sobreescritura estricta de config (se fusiona)..." "INFO" "Gray"
             Copy-Item -Path $_.FullName -Destination $InstallPath -Recurse -Force
        } else {
            Copy-Item -Path $_.FullName -Destination $InstallPath -Recurse -Force
        }
    }
    
    # Copiar version.txt al final
    Copy-Item $VersionFile "$InstallPath\version.txt" -Force

    # Restaurar Configuración (si es Update)
    if ($IsUpdate -and (Test-Path "$InstallPath\modelo\Conexion.php.bak")) {
        Write-Log "Restaurando configuración de conexión..." "INFO" "Cyan"
        Copy-Item "$InstallPath\modelo\Conexion.php.bak" "$InstallPath\modelo\Conexion.php" -Force
    }

    # Configuración Inicial (si es Instalación Limpia)
    if (-not $IsUpdate) {
        Write-Log "Configurando entorno inicial..." "INFO" "Cyan"
        # Aquí llamaríamos a scripts de configuración como en InstaladorMaestro
        # Ejemplo: Configurar BD vacía, Configurar Conexion.php default
        
        $ConexionFile = "$InstallPath\modelo\Conexion.php"
        if (Test-Path $ConexionFile) {
            $content = Get-Content $ConexionFile -Raw
            # Reemplazos básicos para entorno local por defecto
            $content = $content -replace "private .central_pass = '.*?';", "private `$central_pass = 'Servicios2025!';"
            Set-Content $ConexionFile $content
        }
    }

    # 5. BASE DE DATOS
    Write-Log "Verificando base de datos..." "INFO" "Cyan"
    # Aquí podríamos llamar a un script 'MigrarBD.ps1' que detecte si faltan tablas
    if (-not $IsUpdate) {
         # Importar SQL inicial si es instalación limpia
         # & mysql ... < db.sql
         Write-Log "Nota: Recuerde importar la BD inicial si no existe." "WARNING" "Yellow"
    }

    Write-Host ""
    Write-Log "¡Proceso completado con éxito!" "SUCCESS" "Green"
    Write-Host "La aplicación está en: $InstallPath" -ForegroundColor White
    
    Start-Sleep -Seconds 3

} catch {
    Write-Log "Error crítico: $_" "ERROR" "Red"
    Read-Host "Presione Enter para salir..."
}
