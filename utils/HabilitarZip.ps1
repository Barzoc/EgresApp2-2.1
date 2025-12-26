# Script para habilitar la extensión ZIP en PHP
Write-Host "=== Habilitando extensión ZIP en PHP ===" -ForegroundColor Cyan

$phpIniPath = "C:\xampp\php\php.ini"

if (!(Test-Path $phpIniPath)) {
    Write-Host "❌ No se encontró php.ini en: $phpIniPath" -ForegroundColor Red
    exit 1
}

# Leer contenido
$content = Get-Content $phpIniPath -Raw

# Descomentar ;extension=zip
if ($content -match ";extension=zip") {
    Write-Host "📝 Descomentando extension=zip..." -ForegroundColor Yellow
    $content = $content -replace ";extension=zip", "extension=zip"
    
    # Guardar cambios
    Set-Content -Path $phpIniPath -Value $content -NoNewline
    Write-Host "✅ Extensión ZIP habilitada en php.ini" -ForegroundColor Green
    
    # Reiniciar Apache
    Write-Host "🔄 Reiniciando Apache..." -ForegroundColor Yellow
    
    $apacheService = Get-Service -Name "Apache*" -ErrorAction SilentlyContinue
    if ($apacheService) {
        Restart-Service $apacheService.Name
        Write-Host "✅ Apache reiniciado" -ForegroundColor Green
    } else {
        Write-Host "⚠️  No se encontró el servicio de Apache" -ForegroundColor Yellow
        Write-Host "   Por favor reinicia Apache manualmente desde el panel de XAMPP" -ForegroundColor Yellow
    }
    
    Write-Host "`n✅ Configuración completada!" -ForegroundColor Green
    Write-Host "   Ahora puedes ejecutar: php utils\ProbarCertificadoWord.php" -ForegroundColor Cyan
} else {
    Write-Host "✅ La extensión ZIP ya está habilitada" -ForegroundColor Green
}
