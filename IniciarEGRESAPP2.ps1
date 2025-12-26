# Script de Inicio Automatico - EGRESAPP2
# Este script inicia Laragon y abre la plataforma automaticamente

Write-Host "=== Iniciando EGRESAPP2 ===" -ForegroundColor Cyan
Write-Host ""

# Ruta de Laragon
$laragonPath = "C:\laragon\laragon.exe"

# Verificar si Laragon esta instalado
if (-not (Test-Path $laragonPath)) {
    Write-Host "ERROR: No se encontro Laragon en $laragonPath" -ForegroundColor Red
    Write-Host "Por favor, ajusta la ruta en el script." -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit
}

# Verificar si Laragon ya esta ejecutandose
$laragonRunning = Get-Process -Name "laragon" -ErrorAction SilentlyContinue

if ($laragonRunning) {
    Write-Host "Laragon ya esta ejecutandose" -ForegroundColor Green
} else {
    Write-Host "Iniciando Laragon..." -ForegroundColor Yellow
    Start-Process $laragonPath
    Write-Host "Laragon iniciado" -ForegroundColor Green
    
    # Esperar a que Laragon se inicie completamente
    Write-Host "Esperando a que los servicios se inicien..." -ForegroundColor Yellow
    Start-Sleep -Seconds 5
}

# Iniciar servicios de Laragon
Write-Host "Iniciando servicios Apache y MySQL..." -ForegroundColor Yellow

# Intentar iniciar servicios usando comandos de Laragon
Start-Process $laragonPath -ArgumentList "start" -WindowStyle Hidden -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3

# Verificar si Apache esta corriendo
$apacheRunning = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
if ($apacheRunning) {
    Write-Host "Apache esta ejecutandose" -ForegroundColor Green
} else {
    Write-Host "Apache no se detecto. Puede que necesite iniciarse manualmente desde Laragon." -ForegroundColor Yellow
}

# Verificar si MySQL esta corriendo
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlRunning) {
    Write-Host "MySQL esta ejecutandose" -ForegroundColor Green
} else {
    Write-Host "MySQL no se detecto. Puede que necesite iniciarse manualmente desde Laragon." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Esperando 3 segundos antes de abrir el navegador..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

# Abrir la plataforma en el navegador predeterminado
$url = "http://localhost/EGRESAPP2/index.php"
Write-Host "Abriendo la plataforma en el navegador..." -ForegroundColor Yellow
Start-Process $url

Write-Host ""
Write-Host "=== EGRESAPP2 Iniciado Correctamente ===" -ForegroundColor Green
Write-Host ""
Write-Host "La plataforma deberia abrirse en tu navegador." -ForegroundColor Cyan
Write-Host "Si no se abre automaticamente, visita: $url" -ForegroundColor Cyan
Write-Host ""
Write-Host "Presiona cualquier tecla para cerrar esta ventana..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
