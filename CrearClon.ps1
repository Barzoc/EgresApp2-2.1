#Requires -RunAsAdministrator

Write-Host "===========================================================" -ForegroundColor Cyan
Write-Host "   CREADOR DE CLON COMPLETO - EGRESAPP2" -ForegroundColor Yellow
Write-Host "===========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Este script creará un archivo ZIP que contiene:" -ForegroundColor White
Write-Host "  1. Todo el código fuente del sistema." -ForegroundColor Gray
Write-Host "  2. Una COPIA EXACTA de tu Base de Datos actual." -ForegroundColor Gray
Write-Host "  3. El instalador automático para el nuevo PC." -ForegroundColor Gray
Write-Host ""

# 1. Exportar la Base de Datos
Write-Host "Paso 1: Exportando Base de Datos..." -ForegroundColor Yellow
$scriptDB = "$PSScriptRoot\ExportarBaseDatos.ps1"
if (Test-Path $scriptDB) {
    & $scriptDB
} else {
    Write-Host "Error: No encuentro ExportarBaseDatos.ps1" -ForegroundColor Red
    pause
    exit
}

# 2. Empaquetar todo
Write-Host ""
Write-Host "Paso 2: Creando el paquete ZIP (esto tardará unos minutos)..." -ForegroundColor Yellow

$TimeStamp = Get-Date -Format "yyyy-MM-dd_HH-mm"
$ZipName = "EGRESAPP2_CLON_$TimeStamp.zip"
$Source = "C:\laragon\www\EGRESAPP2"
$Exclude = @("*.zip", "*.log", "tmp", ".git", ".vscode", "output", "certificados_generados")

# Mensaje de espera
Write-Host "Comprimiendo carpeta $Source..." -ForegroundColor Cyan

# Comando de compresión (excluyendo basura)
Compress-Archive -Path "$Source\*" -DestinationPath "$PSScriptRoot\$ZipName" -Force

Write-Host ""
Write-Host "===========================================================" -ForegroundColor Green
Write-Host "   ¡CLON CREADO EXITOSAMENTE!" -ForegroundColor Green
Write-Host "===========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Archivo generado: $PSScriptRoot\$ZipName" -ForegroundColor Yellow
Write-Host ""
Write-Host "INSTRUCCIONES PARA EL NUEVO PC:" -ForegroundColor Cyan
Write-Host "1. Copia este archivo ZIP al nuevo computador."
Write-Host "2. Descomprímelo."
Write-Host "3. Ejecuta 'InstaladorMaestro.bat' como Administrador."
Write-Host "4. LISTO. El instalador configurará las rutas automáticamente"
Write-Host "   y cargará todos tus datos."
Write-Host ""
pause
