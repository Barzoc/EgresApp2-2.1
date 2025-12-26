#Requires -RunAsAdministrator

# ============================================================================
# INSTALADOR MAESTRO - EGRESAPP2
# ============================================================================
# Este es el instalador principal que ejecuta todo el proceso de instalaciÃ³n
# de forma automÃ¡tica y sin intervenciÃ³n del usuario
# ============================================================================

# ConfiguraciÃ³n de colores
$Host.UI.RawUI.BackgroundColor = "Black"
$Host.UI.RawUI.ForegroundColor = "White"
Clear-Host

# Banner
Write-Host "â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—" -ForegroundColor Cyan
Write-Host "â•‘                                                                          â•‘" -ForegroundColor Cyan
Write-Host "â•‘                  INSTALADOR AUTOMÃTICO - EGRESAPP2                       â•‘" -ForegroundColor Yellow
Write-Host "â•‘                                                                          â•‘" -ForegroundColor Cyan
Write-Host "â•‘              Sistema de GestiÃ³n de Egresados                             â•‘" -ForegroundColor White
Write-Host "â•‘                                                                          â•‘" -ForegroundColor Cyan
Write-Host "â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•" -ForegroundColor Cyan
Write-Host ""
Write-Host "Este instalador configurarÃ¡ automÃ¡ticamente:" -ForegroundColor White
Write-Host "  â€¢ Laragon (PHP + MySQL + Apache)" -ForegroundColor Cyan
Write-Host "  â€¢ Composer" -ForegroundColor Cyan
Write-Host "  â€¢ Tesseract OCR" -ForegroundColor Cyan
Write-Host "  â€¢ ImageMagick" -ForegroundColor Cyan
Write-Host "  â€¢ LibreOffice" -ForegroundColor Cyan
Write-Host "  â€¢ Base de datos" -ForegroundColor Cyan
Write-Host "  â€¢ Acceso directo en el escritorio" -ForegroundColor Cyan
Write-Host ""
Write-Host "Tiempo estimado: 15-30 minutos" -ForegroundColor Yellow
Write-Host ""
Write-Host "Presione cualquier tecla para comenzar la instalaciÃ³n..." -ForegroundColor Green
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
Clear-Host

# Variables globales
$ScriptRoot = $PSScriptRoot
$LogFile = "$ScriptRoot\instalacion_completa_log.txt"
$ErrorCount = 0
$SuccessCount = 0
$StartTime = Get-Date

# FunciÃ³n de logging
function Write-Log {
    param($Message, $Type = "INFO")
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogMessage = "[$Timestamp] [$Type] $Message"
    Add-Content -Path $LogFile -Value $LogMessage
    
    switch ($Type) {
        "SUCCESS" { Write-Host "[OK] $Message" -ForegroundColor Green }
        "ERROR" { Write-Host "[ERROR] $Message" -ForegroundColor Red }
        "WARNING" { Write-Host "[WARN] $Message" -ForegroundColor Yellow }
        "INFO" { Write-Host "[INFO] $Message" -ForegroundColor Cyan }
        "STEP" { Write-Host "`n=== $Message ===" -ForegroundColor Magenta }
        default { Write-Host "  $Message" -ForegroundColor White }
    }
}

# FunciÃ³n para ejecutar script y verificar resultado
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
        }
        else {
            Write-Log "$Description fallÃ³ con cÃ³digo: $LASTEXITCODE" "ERROR"
            $script:ErrorCount++
            return $false
        }
    }
    catch {
        Write-Log "$Description fallÃ³: $_" "ERROR"
        $script:ErrorCount++
        return $false
    }
}

# ============================================================================
# INICIO DE LA INSTALACIÃ“N
# ============================================================================

Write-Log "Iniciando instalaciÃ³n completa de EGRESAPP2" "INFO"
Write-Log "Directorio de instalaciÃ³n: $ScriptRoot" "INFO"
Write-Host ""

# ============================================================================
# PASO 1: INSTALAR LARAGON
# ============================================================================
$laragonInstalled = Invoke-InstallScript `
    -ScriptPath "$ScriptRoot\InstalarLaragon.ps1" `
    -Description "PASO 1/6: Instalando Laragon (PHP + MySQL + Apache)"

if (-not $laragonInstalled) {
    Write-Log "Laragon es requerido para continuar. InstalaciÃ³n abortada." "ERROR"
    Write-Host ""
    Write-Host "Presione cualquier tecla para salir..." -ForegroundColor Red
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

Start-Sleep -Seconds 3

# ============================================================================
# PASO 2: COPIAR ARCHIVOS DEL PROYECTO
# ============================================================================
Write-Log "PASO 2/6: Copiando archivos del proyecto" "STEP"

$DestinationPath = "C:\laragon\www\EGRESAPP2"

if ($ScriptRoot -ne $DestinationPath) {
    try {
        if (-not (Test-Path $DestinationPath)) {
            New-Item -ItemType Directory -Path $DestinationPath -Force | Out-Null
        }
        
        Write-Log "Copiando archivos a $DestinationPath..." "INFO"
        Copy-Item -Path "$ScriptRoot\*" -Destination $DestinationPath -Recurse -Force -Exclude @("instalacion_*.txt", "*.log")
        
        Write-Log "Archivos copiados correctamente" "SUCCESS"
        $SuccessCount++
        
        # Actualizar ScriptRoot para los siguientes pasos
        $ScriptRoot = $DestinationPath
    }
    catch {
        Write-Log "Error al copiar archivos: $_" "ERROR"
        $ErrorCount++
    }
}
else {
    Write-Log "Los archivos ya estÃ¡n en la ubicaciÃ³n correcta" "SUCCESS"
    $SuccessCount++
}

Start-Sleep -Seconds 2

# ============================================================================
# PASO 3: INSTALAR DEPENDENCIAS
# ============================================================================
Invoke-InstallScript `
    -ScriptPath "$DestinationPath\InstalarDependencias.ps1" `
    -Description "PASO 3/6: Instalando dependencias (Composer, Tesseract, ImageMagick, LibreOffice)"

Start-Sleep -Seconds 3

# ============================================================================
# PASO 4: IMPORTAR BASE DE DATOS
# ============================================================================
Invoke-InstallScript `
    -ScriptPath "$DestinationPath\ImportarBaseDatos.ps1" `
    -Description "PASO 4/6: Importando base de datos"

Start-Sleep -Seconds 2

# ============================================================================
# PASO 5: CONFIGURAR CONEXIÃ“N A BASE DE DATOS
# ============================================================================
Write-Log "PASO 5/6: Configurando conexiÃ³n a base de datos" "STEP"

$conexionFile = "$DestinationPath\modelo\Conexion.php"
if (Test-Path $conexionFile) {
    try {
        $content = Get-Content $conexionFile -Raw
        
        # Asegurar configuraciÃ³n correcta para Laragon usando una sintaxis mÃ¡s segura
        # Evitamos problemas con palabras reservadas de PowerShell
        $hostPattern = 'private \$host\s*=\s*[''"].*?[''"]\s*;'
        $userPattern = 'private \$user\s*=\s*[''"].*?[''"]\s*;'
        $passPattern = 'private \$pass\s*=\s*[''"].*?[''"]\s*;'
        $dbnamePattern = 'private \$dbname\s*=\s*[''"].*?[''"]\s*;'
        
        $content = $content -replace $hostPattern, 'private $host = ''localhost'';'
        $content = $content -replace $userPattern, 'private $user = ''root'';'
        $content = $content -replace $passPattern, 'private $pass = '''';'
        $content = $content -replace $dbnamePattern, 'private $dbname = ''gestion_egresados'';'
        
        Set-Content -Path $conexionFile -Value $content
        
        Write-Log "ConexiÃ³n a base de datos configurada" "SUCCESS"
        $SuccessCount++
    }
    catch {
        Write-Log "Error al configurar conexiÃ³n: $_" "WARNING"
    }
}
else {
    Write-Log "Archivo Conexion.php no encontrado" "WARNING"
}


Start-Sleep -Seconds 2

# ============================================================================
# PASO 6: CREAR ACCESO DIRECTO EN EL ESCRITORIO
# ============================================================================
Invoke-InstallScript `
    -ScriptPath "$DestinationPath\CrearAccesoDirecto.ps1" `
    -Description "PASO 6/6: Creando acceso directo en el escritorio"

# ============================================================================
# RESUMEN FINAL
# ============================================================================
$EndTime = Get-Date
$Duration = $EndTime - $StartTime

Clear-Host
Write-Host ""
Write-Host "â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—" -ForegroundColor Cyan
Write-Host "â•‘                                                                          â•‘" -ForegroundColor Cyan
Write-Host "â•‘                    INSTALACIÃ“N COMPLETADA                                â•‘" -ForegroundColor Green
Write-Host "â•‘                                                                          â•‘" -ForegroundColor Cyan
Write-Host "â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•" -ForegroundColor Cyan
Write-Host ""
Write-Host "RESUMEN DE INSTALACIÃ“N" -ForegroundColor Yellow
Write-Host "â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”" -ForegroundColor Gray
Write-Host ""
Write-Host "  Instalaciones exitosas: " -NoNewline -ForegroundColor White
Write-Host "$SuccessCount" -ForegroundColor Green
Write-Host "  Errores encontrados:     " -NoNewline -ForegroundColor White
Write-Host "$ErrorCount" -ForegroundColor $(if ($ErrorCount -gt 0) { "Red" } else { "Green" })
Write-Host "  Tiempo total:            " -NoNewline -ForegroundColor White
Write-Host "$($Duration.Minutes) minutos $($Duration.Seconds) segundos" -ForegroundColor Cyan
Write-Host ""
Write-Host "â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”" -ForegroundColor Gray
Write-Host ""

if ($ErrorCount -eq 0) {
    Write-Host "[OK] EGRESAPP2 esta listo para usar!" -ForegroundColor Green
    Write-Host ""
    Write-Host "COMO INICIAR LA APLICACION:" -ForegroundColor Yellow
    Write-Host "  1. Haga doble clic en el icono 'EGRESAPP2' en su escritorio" -ForegroundColor White
    Write-Host "  2. La aplicacion se abrira automaticamente en su navegador" -ForegroundColor White
    Write-Host ""
    Write-Host "CREDENCIALES POR DEFECTO:" -ForegroundColor Yellow
    Write-Host "  Email:      admin@test.com" -ForegroundColor White
    Write-Host "  Contrasena: admin123" -ForegroundColor White
}
else {
    Write-Host "[WARN] La instalacion se completo con algunos errores" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Revise el archivo de log para mas detalles:" -ForegroundColor White
    Write-Host "  $LogFile" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”" -ForegroundColor Gray
Write-Host ""
Write-Host "Presione cualquier tecla para salir..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

