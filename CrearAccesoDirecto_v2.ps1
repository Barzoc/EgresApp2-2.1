#Requires -RunAsAdministrator

# ============================================================================
# CREADOR DE ACCESO DIRECTO MEJORADO - EGRESAPP2
# ============================================================================
# Crea acceso directo en el escritorio que inicia todo automáticamente
# ============================================================================

param(
    [string]$DesktopPath = [Environment]::GetFolderPath("Desktop"),
    [string]$ProjectPath = "$PSScriptRoot"
)

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CREANDO ACCESO DIRECTO - EGRESAPP2" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Crear objeto WScript.Shell
$WshShell = New-Object -ComObject WScript.Shell

# Ruta del acceso directo
$ShortcutPath = "$DesktopPath\🚀 EGRESAPP2.lnk"

# Crear acceso directo
$Shortcut = $WshShell.CreateShortcut($ShortcutPath)
$Shortcut.TargetPath = "PowerShell.exe"
$Shortcut.Arguments = "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$ProjectPath\LauncherAutomatico.ps1`" -Silent -NoWait"
$Shortcut.WorkingDirectory = $ProjectPath
$Shortcut.Description = "Inicia EGRESAPP2 automáticamente (Apache + MySQL + Navegador)"
$Shortcut.IconLocation = "$ProjectPath\assets\img\imagenes\icon.png"
$Shortcut.WindowStyle = 7 # Minimizado
$Shortcut.Save()

if (Test-Path $ShortcutPath) {
    Write-Host "✓ Acceso directo creado exitosamente" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ubicación: $ShortcutPath" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "El acceso directo ejecutará:" -ForegroundColor Yellow
    Write-Host "  1. Iniciará Laragon (Apache + MySQL)" -ForegroundColor White
    Write-Host "  2. Verificará la conexión a la base de datos" -ForegroundColor White
    Write-Host "  3. Abrirá EGRESAPP2 en el navegador" -ForegroundColor White
    Write-Host ""
    Write-Host "✅ ¡Todo listo! Haga doble clic en el icono del escritorio" -ForegroundColor Green
} else {
    Write-Host "✗ Error al crear acceso directo" -ForegroundColor Red
}

Write-Host ""
Write-Host "Presione cualquier tecla para continuar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
