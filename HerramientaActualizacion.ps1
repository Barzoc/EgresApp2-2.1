#Requires -RunAsAdministrator

# ============================================================================
# HERRAMIENTA DE ACTUALIZACIÓN (PARCHER) - EGRESAPP2
# ============================================================================
# Objetivo: Aplicar actualizaciones de código y BD sobre una instalación existente
# sin borrar datos de usuario.
# ============================================================================

$Host.UI.RawUI.BackgroundColor = "DarkBlue"
Clear-Host

$ScriptRoot = $PSScriptRoot
$TargetDir = "C:\laragon\www\EGRESAPP2" # Se intentará detectar

function Write-Log { param($Msg, $Color="White") Write-Host "[UPDATE] $Msg" -ForegroundColor $Color }

Write-Host "=== ACTUALIZADOR EGRESAPP2 ===" -ForegroundColor Cyan

# 1. Buscando instalación
if (-not (Test-Path $TargetDir)) {
    Write-Log "No se encontró instalación estándar en $TargetDir" "Yellow"
    $TargetDir = Read-Host "Ingrese la ruta de la carpeta EGRESAPP2 a actualizar"
}

if (-not (Test-Path "$TargetDir\modelo\Conexion.php")) {
    Write-Log "Error: No parece ser una carpeta válida de EGRESAPP2 (falta Conexion.php)" "Red"
    exit
}

Write-Log "Actualizando instalación en: $TargetDir" "Green"

# 2. Backup Rápido
$BackupDir = "$TargetDir\..\backup_update_$(Get-Date -Format 'yyyyMMdd_HHmm')"
Write-Log "Creando respaldo temporal en $BackupDir..." "Cyan"
New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
Copy-Item "$TargetDir\modelo\Conexion.php" "$BackupDir\Conexion.php"
# Podríamos hacer dump de la BD aquí también

# 3. Aplicar Archivos
Write-Log "Aplicando nuevos archivos..." "Cyan"
$Exclude = @("assets/expedientes", "certificados", "config/config_nodo.php", "logs", ".git")
$Source = $ScriptRoot

# Copia usando Robocopy para eficiencia y exclusión (wrapper simple en PS)
# Para simplificar en PS puro:
Get-ChildItem $Source -Recurse | Where-Object { 
    $RelPath = $_.FullName.Substring($Source.Length)
    # Filtrar exclusiones burdas
    -not ($RelPath -match "assets\\expedientes" -or $RelPath -match "certificados")
} | ForEach-Object {
    $Dest = "$TargetDir$($_.FullName.Substring($Source.Length))"
    if ($_.PSIsContainer) {
        if (-not (Test-Path $Dest)) { New-Item -ItemType Directory -Path $Dest -Force | Out-Null }
    } else {
        Copy-Item $_.FullName $Dest -Force
    }
}

# 4. Restaurar Config Crítica (si fue sobrescrita por error, aunque intentamos evitarlo)
# En este caso, Conexion.php suele ser parte del código fuente, pero con credenciales locales.
# Si el parche trae un Conexion.php nuevo (ej. nueva estructura), hay que tener cuidado.
# Asumiremos que el parche NO trae Conexion.php o que el usuario debe fusionarlo.
# Pero por seguridad restauramos el backup si existe diferencia crítica.
if (Test-Path "$BackupDir\Conexion.php") {
    # Comparar o preguntar. Por defecto en parches, mantenemos la config del usuario.
    Copy-Item "$BackupDir\Conexion.php" "$TargetDir\modelo\Conexion.php" -Force
    Write-Log "Configuración de conexión restaurada." "Green"
}

Write-Log "Actualización completada." "Green"
Write-Log "Verifique funcionamiento." "Yellow"
Start-Sleep -Seconds 3
