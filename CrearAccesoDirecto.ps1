# ============================================================================
# CREADOR DE ACCESO DIRECTO EN EL ESCRITORIO - EGRESAPP2
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   CREANDO ACCESO DIRECTO EN EL ESCRITORIO" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

# Rutas
$DesktopPath = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = "$DesktopPath\EGRESAPP2.lnk"
$LauncherScript = "$PSScriptRoot\EGRESAPP2_Launcher.ps1"
$IconPath = "$PSScriptRoot\assets\egresapp2_icon.ico"

# Verificar que el launcher existe
if (-not (Test-Path $LauncherScript)) {
    Write-Host "Error: No se encontro el script launcher: $LauncherScript" -ForegroundColor Red
    exit 1
}

Write-Host "Creando acceso directo..." -ForegroundColor Yellow

try {
    # Crear objeto WScript.Shell
    $WScriptShell = New-Object -ComObject WScript.Shell
    $Shortcut = $WScriptShell.CreateShortcut($ShortcutPath)
    
    # Configurar el acceso directo para ejecutar PowerShell de forma oculta
    $Shortcut.TargetPath = "powershell.exe"
    $Shortcut.Arguments = "-WindowStyle Hidden -ExecutionPolicy Bypass -File `"$LauncherScript`""
    $Shortcut.WorkingDirectory = "$PSScriptRoot"
    $Shortcut.Description = "Iniciar EGRESAPP2"
    
    # Asignar icono si existe
    if (Test-Path $IconPath) {
        $Shortcut.IconLocation = $IconPath
    }
    else {
        # Usar icono predeterminado de Laragon si no hay icono personalizado
        $LaragonIcon = "C:\laragon\laragon.exe,0"
        if (Test-Path "C:\laragon\laragon.exe") {
            $Shortcut.IconLocation = $LaragonIcon
        }
    }
    
    # Guardar el acceso directo
    $Shortcut.Save()
    
    Write-Host "Acceso directo creado en el escritorio" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ubicacion: $ShortcutPath" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Ahora puede hacer doble clic en 'EGRESAPP2' en su escritorio" -ForegroundColor Green
    Write-Host "para iniciar la aplicacion automaticamente." -ForegroundColor Green
    
    exit 0
    
}
catch {
    Write-Host "Error al crear acceso directo: $_" -ForegroundColor Red
    exit 1
}
