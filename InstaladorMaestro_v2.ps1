#Requires -RunAsAdministrator

# ============================================================================
# INSTALADOR MAESTRO V2 - EGRESAPP2
# ============================================================================
# Versión mejorada con pre-flight checks y rollback automático
# ============================================================================

$Host.UI.RawUI.BackgroundColor = "Black"
$Host.UI.RawUI.ForegroundColor = "White"
Clear-Host

# Variables globales
$ScriptRoot = $PSScriptRoot
$LogFile = "$ScriptRoot\instalacion_completa_log.txt"
$CheckpointFile = "$ScriptRoot\installation_checkpoint.json"
$ErrorCount = 0
$SuccessCount = 0
$StartTime = Get-Date

# Banner
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "║       INSTALADOR AUTOMÁTICO V2 - EGRESAPP2            ║" -ForegroundColor Yellow
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "║          Sistema de Gestión de Egresados              ║" -ForegroundColor White
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "Este instalador configurará automáticamente:" -ForegroundColor White
Write-Host "  ✓ Laragon (PHP + MySQL + Apache)" -ForegroundColor Cyan
Write-Host "  ✓ Composer y dependencias PHP" -ForegroundColor Cyan
Write-Host "  ✓ Tesseract OCR + ImageMagick" -ForegroundColor Cyan
Write-Host "  ✓ LibreOffice" -ForegroundColor Cyan
Write-Host "  ✓ Base de datos con backup automático" -ForegroundColor Cyan
Write-Host "  ✓ Acceso directo en el escritorio" -ForegroundColor Cyan
Write-Host ""
Write-Host "Tiempo estimado: 15-30 minutos" -ForegroundColor Yellow
Write-Host ""
Write-Host "Presione cualquier tecla para comenzar..." -ForegroundColor Green
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
Clear-Host

# ============================================================================
# FUNCIONES
# ============================================================================

function Write-Log {
    param($Message, $Type = "INFO")
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogMessage = "[$Timestamp] [$Type] $Message"
    Add-Content -Path $LogFile -Value $LogMessage
    
    switch ($Type) {
        "SUCCESS" { Write-Host "[OK] $Message" -ForegroundColor Green }
        "ERROR" { Write-Host "[X] $Message" -ForegroundColor Red }
        "WARNING" { Write-Host "[!] $Message" -ForegroundColor Yellow }
        "INFO" { Write-Host "[i] $Message" -ForegroundColor Cyan }
        "STEP" { Write-Host "`n====== $Message ======" -ForegroundColor Magenta }
        default { Write-Host "  $Message" -ForegroundColor White }
    }
}

function Save-Checkpoint {
    param([string]$Phase, [hashtable]$Data)
    
    $checkpoint = @{
        Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        Phase = $Phase
        Data = $Data
    } | ConvertTo-Json
    
    $checkpoint | Out-File $CheckpointFile
    Write-Log "Checkpoint guardado: $Phase" "INFO"
}

function Invoke-Rollback {
    param([string]$Reason)
    
    Write-Host ""
    Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Red
    Write-Host "║            INICIANDO ROLLBACK                         ║" -ForegroundColor Yellow
    Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Red
    Write-Host ""
    Write-Host "Razón: $Reason" -ForegroundColor White
    Write-Host ""
    
    if (-not (Test-Path $CheckpointFile)) {
        Write-Log "No hay checkpoint, rollback manual requerido" "WARNING"
        return
    }
    
    $checkpoint = Get-Content $CheckpointFile | ConvertFrom-Json
    Write-Log "Rollback desde fase: $($checkpoint.Phase)" "INFO"
    
    switch ($checkpoint.Phase) {
        "PRE_INSTALL" {
            Write-Log "No se realizaron cambios, no es necesario rollback" "SUCCESS"
        }
        "POST_DB_IMPORT" {
            Write-Log "Restaurando base de datos desde backup..." "WARNING"
            
            $backupPath = $checkpoint.Data.BackupPath
            $dbName = $checkpoint.Data.DatabaseName
            $mysqlExe = $checkpoint.Data.MySQLPath
            
            if (Test-Path $backupPath) {
                & $mysqlExe -u root -e "DROP DATABASE IF EXISTS ``$dbName``;"
                & $mysqlExe -u root -e "CREATE DATABASE ``$dbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
                Get-Content $backupPath | & $mysqlExe -u root $dbName
                
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Base de datos restaurada" "SUCCESS"
                } else {
                    Write-Log "Error al restaurar BD" "ERROR"
                }
                
                Remove-Item $backupPath -Force -ErrorAction SilentlyContinue
            }
        }
        "FILES_COPIED" {
            Write-Log "Eliminando archivos copiados..." "WARNING"
            $destPath = $checkpoint.Data.DestinationPath
            if (Test-Path $destPath) {
                Remove-Item -Path $destPath -Recurse -Force
                Write-Log "Archivos eliminados" "SUCCESS"
            }
        }
    }
    
    Remove-Item $CheckpointFile -Force -ErrorAction SilentlyContinue
    Write-Host ""
    Write-Host "Rollback completado" -ForegroundColor Yellow
}

function Invoke-InstallScript {
    param(
        [string]$ScriptPath,
        [string]$Description
    )
    
    Write-Log $Description "STEP"
    
    if (-not (Test-Path $ScriptPath)) {
        Write-Log "Script no encontrado: $ScriptPath" "ERROR"
        $script:ErrorCount++
        return $false
    }
    
    try {
        & $ScriptPath
        if ($LASTEXITCODE -eq 0 -or $null -eq $LASTEXITCODE) {
            Write-Log "$Description completado" "SUCCESS"
            $script:SuccessCount++
            return $true
        } else {
            Write-Log "$Description falló con código: $LASTEXITCODE" "ERROR"
            $script:ErrorCount++
            return $false
        }
    } catch {
        Write-Log "$Description falló: $_" "ERROR"
        $script:ErrorCount++
        return $false
    }
}

# ============================================================================
# INICIO DE INSTALACIÓN
# ============================================================================

Write-Log "Iniciando instalación completa de EGRESAPP2" "INFO"
Write-Log "Directorio: $ScriptRoot" "INFO"

try {
    # ========================================================================
    # PASO 0: PRE-FLIGHT CHECKS
    # ========================================================================
    Write-Log "PASO 0/7: Verificaciones previas (Pre-Flight Checks)" "STEP"
    
    $preflightScript = "$ScriptRoot\scripts\PreFlightChecks.ps1"
    if (Test-Path $preflightScript) {
        & $preflightScript -ProjectRoot $ScriptRoot
        
        if ($LASTEXITCODE -gt 1) {
            Write-Log "Pre-flight checks fallaron críticamente" "ERROR"
            Write-Host ""
            Write-Host "No se puede continuar con la instalación." -ForegroundColor Red
            Write-Host "Corrija los errores y vuelva a ejecutar." -ForegroundColor Yellow
            Write-Host ""
            Write-Host "Presione cualquier tecla para salir..."
            $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
            exit 1
        } elseif ($LASTEXITCODE -eq 1) {
            Write-Host ""
            Write-Host "Algunos checks fallaron. ¿Desea continuar de todos modos? (S/N)" -ForegroundColor Yellow
            $continuar = Read-Host
            if ($continuar -ne "S" -and $continuar -ne "s") {
                Write-Log "Usuario canceló instalación" "INFO"
                exit 0
            }
        }
    } else {
        Write-Log "Pre-flight checks no disponibles, continuando..." "WARNING"
    }
    
    Save-Checkpoint -Phase "PRE_INSTALL" -Data @{}
    Start-Sleep -Seconds 2
    
    # ========================================================================
    # PASO 1: INSTALAR LARAGON
    # ========================================================================
    $laragonInstalled = Invoke-InstallScript `
        -ScriptPath "$ScriptRoot\InstalarLaragon.ps1" `
        -Description "PASO 1/7: Instalando Laragon (PHP + MySQL + Apache)"
    
    if (-not $laragonInstalled) {
        Write-Log "Laragon es requerido. Instalación abortada." "ERROR"
        Invoke-Rollback -Reason "Laragon no se instaló correctamente"
        exit 1
    }
    
    Start-Sleep -Seconds 3
    
    # ========================================================================
    # PASO 2: COPIAR ARCHIVOS
    # ========================================================================
    Write-Log "PASO 2/7: Copiando archivos del proyecto" "STEP"
    
    $DestinationPath = "C:\laragon\www\EGRESAPP2"
    
    if ($ScriptRoot -ne $DestinationPath) {
        if (-not (Test-Path $DestinationPath)) {
            New-Item -ItemType Directory -Path $DestinationPath -Force | Out-Null
        }
        
        Write-Log "Copiando archivos a $DestinationPath..." "INFO"
        Copy-Item -Path "$ScriptRoot\*" -Destination $DestinationPath -Recurse -Force -Exclude @("instalacion_*.txt", "*.log", "installation_checkpoint.json")
        
        Write-Log "Archivos copiados correctamente" "SUCCESS"
        $SuccessCount++
        
        Save-Checkpoint -Phase "FILES_COPIED" -Data @{
            DestinationPath = $DestinationPath
        }
        
        $ScriptRoot = $DestinationPath
    } else {
        Write-Log "Archivos ya están en la ubicación correcta" "SUCCESS"
        $SuccessCount++
    }
    
    Start-Sleep -Seconds 2
    
    # ========================================================================
    # PASO 3: INSTALAR DEPENDENCIAS
    # ========================================================================
    Invoke-InstallScript `
        -ScriptPath "$DestinationPath\InstalarDependencias.ps1" `
        -Description "PASO 3/7: Instalando dependencias"
    
    Start-Sleep -Seconds 3
    
    # ========================================================================
    # PASO 4: CREAR BACKUP DE BD (si existe)
    # ========================================================================
    Write-Log "PASO 4/7: Creando backup de seguridad" "STEP"
    
    $mysqlLocations = @(
        "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe",
        "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
    )
    
    $MySQLPath = $null
    foreach ($location in $mysqlLocations) {
        if (Test-Path $location) {
            $MySQLPath = $location
            break
        }
    }
    
    $backupPath = "$DestinationPath\backup_pre_import_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
    
    if ($MySQLPath) {
        # Verificar si la BD ya existe
        $dbExists = & $MySQLPath -u root -e "SHOW DATABASES LIKE 'gestion_egresados';" 2>&1
        if ($dbExists -match "gestion_egresados") {
            Write-Log "Base de datos existente detectada, creando backup..." "INFO"
            $mysqldump = $MySQLPath.Replace("mysql.exe", "mysqldump.exe")
            & $mysqldump -u root gestion_egresados > $backupPath 2>&1
            
            if ($LASTEXITCODE -eq 0) {
                Write-Log "Backup creado en $backupPath" "SUCCESS"
            }
        } else {
            Write-Log "No hay BD existente, se omite backup" "INFO"
        }
    }
    
    # ========================================================================
    # PASO 5: IMPORTAR BASE DE DATOS (PUNTO DE NO RETORNO)
    # ========================================================================
    Write-Host ""
    Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Yellow
    Write-Host "║         ⚠️  PUNTO DE NO RETORNO  ⚠️                     ║" -ForegroundColor Red
    Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "A continuación se importará la base de datos." -ForegroundColor White
    Write-Host "Si existe una BD previa, será reemplazada." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "¿Desea continuar? (S/N)" -ForegroundColor Yellow
    $continuar = Read-Host
    
    if ($continuar -ne "S" -and $continuar -ne "s") {
        Write-Log "Usuario canceló en punto de no retorno" "INFO"
        Invoke-Rollback -Reason "Usuario canceló instalación"
        exit 0
    }
    
    Save-Checkpoint -Phase "POST_DB_IMPORT" -Data @{
        BackupPath = $backupPath
        DatabaseName = "gestion_egresados"
        MySQLPath = $MySQLPath
    }
    
    Invoke-InstallScript `
        -ScriptPath "$DestinationPath\ImportarBaseDatos.ps1" `
        -Description "PASO 5/7: Importando base de datos"
    
    Start-Sleep -Seconds 2
    
    # ========================================================================
    # PASO 6: CONFIGURAR CONEXIÓN
    # ========================================================================
    Write-Log "PASO 6/7: Configurando conexión a base de datos" "STEP"
    
    $conexionFile = "$DestinationPath\modelo\Conexion.php"
    if (Test-Path $conexionFile) {
        try {
            $content = Get-Content $conexionFile -Raw
            
            $content = $content -replace 'private \$host\s*=\s*[''"].*?[''"];', 'private $host = ''localhost'';'
            $content = $content -replace 'private \$user\s*=\s*[''"].*?[''"];', 'private $user = ''root'';'
            $content = $content -replace 'private \$pass\s*=\s*[''"].*?[''"];', 'private $pass = '''';'
            $content = $content -replace 'private \$dbname\s*=\s*[''"].*?[''"];', 'private $dbname = ''gestion_egresados'';'
            
            Set-Content -Path $conexionFile -Value $content
            
            Write-Log "Conexión configurada" "SUCCESS"
            $SuccessCount++
        } catch {
            Write-Log "Error al configurar conexión: $_" "WARNING"
        }
    }
    
    Start-Sleep -Seconds 2
    
    # ========================================================================
    # PASO 7: CREAR ACCESO DIRECTO
    # ========================================================================
    Invoke-InstallScript `
        -ScriptPath "$DestinationPath\CrearAccesoDirecto.ps1" `
        -Description "PASO 7/7: Creando acceso directo"
    
    # Eliminar checkpoint (instalación exitosa)
    if (Test-Path $CheckpointFile) {
        Remove-Item $CheckpointFile -Force
    }
    
    # Eliminar backup temporal si existe
    if (Test-Path $backupPath) {
        Remove-Item $backupPath -Force -ErrorAction SilentlyContinue
    }
    
} catch {
    Write-Log "Error crítico durante instalación: $_" "ERROR"
    Invoke-Rollback -Reason "Excepción no controlada: $_"
    exit 1
}

# ============================================================================
# RESUMEN FINAL
# ============================================================================
$EndTime = Get-Date
$Duration = $EndTime - $StartTime

Clear-Host
Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "║           ✅ INSTALACIÓN COMPLETADA ✅                 ║" -ForegroundColor Green
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "RESUMEN DE INSTALACIÓN" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""
Write-Host "  Instalaciones exitosas: " -NoNewline
Write-Host "$SuccessCount" -ForegroundColor Green
Write-Host "  Errores encontrados:     " -NoNewline
Write-Host "$ErrorCount" -ForegroundColor $(if ($ErrorCount -gt 0) {"Red"} else {"Green"})
Write-Host "  Tiempo total:            " -NoNewline
Write-Host "$($Duration.Minutes)m $($Duration.Seconds)s" -ForegroundColor Cyan
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

if ($ErrorCount -eq 0) {
    Write-Host "✅ EGRESAPP2 está listo para usar!" -ForegroundColor Green
    Write-Host ""
    Write-Host "CÓMO INICIAR LA APLICACIÓN:" -ForegroundColor Yellow
    Write-Host "  1. Haga doble clic en 'EGRESAPP2' en su escritorio" -ForegroundColor White
    Write-Host "  2. La aplicación se abrirá automáticamente" -ForegroundColor White
    Write-Host ""
    Write-Host "CREDENCIALES POR DEFECTO:" -ForegroundColor Yellow
    Write-Host "  Email:      admin@test.com" -ForegroundColor White
    Write-Host "  Contraseña: admin123" -ForegroundColor White
    Write-Host ""
    Write-Host "Se recomienda ejecutar verificación post-instalación:" -ForegroundColor Cyan
    Write-Host "  .\scripts\VerificacionCompleta.ps1" -ForegroundColor White
} else {
    Write-Host "⚠️ La instalación se completó con algunos errores" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Revise el log para más detalles:" -ForegroundColor White
    Write-Host "  $LogFile" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Presione cualquier tecla para salir..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
