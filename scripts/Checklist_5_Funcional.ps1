# ============================================================================
# CHECKLIST 5: VERIFICACIÓN FUNCIONAL - EGRESAPP2
# ============================================================================

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CHECKLIST 5: VERIFICACIÓN FUNCIONAL" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$functionalOk = $true

# 5.1 Acceso a página principal
try {
    $response = Invoke-WebRequest -Uri "http://localhost/EGRESAPP2/" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Página principal accesible (HTTP 200)" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Página responde con código: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ No se puede acceder a la aplicación" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    $functionalOk = $false
}

# 5.2 Verificar que archivos PHP se procesan
try {
    $phpTest = Invoke-WebRequest -Uri "http://localhost/EGRESAPP2/index.php" -UseBasicParsing -TimeoutSec 10
    if ($phpTest.Content -notmatch "<?php") {
        Write-Host "✅ PHP procesando archivos correctamente" -ForegroundColor Green
    } else {
        Write-Host "❌ PHP NO está procesando archivos (se muestra código fuente)" -ForegroundColor Red
        $functionalOk = $false
    }
} catch {
    Write-Host "⚠️ No se pudo verificar procesamiento PHP" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Magenta
Write-Host "  VERIFICACIONES MANUALES REQUERIDAS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Magenta
Write-Host ""
Write-Host "Complete las siguientes verificaciones manualmente:" -ForegroundColor White
Write-Host ""
Write-Host "  1. ☐ Abrir: http://localhost/EGRESAPP2" -ForegroundColor Cyan
Write-Host "  2. ☐ Login con: admin@test.com / admin123" -ForegroundColor Cyan
Write-Host "  3. ☐ Verificar tabla de egresados carga" -ForegroundColor Cyan
Write-Host "  4. ☐ Intentar subir un PDF de expediente" -ForegroundColor Cyan
Write-Host "  5. ☐ Generar un certificado de prueba" -ForegroundColor Cyan
Write-Host ""

# Abrir navegador automáticamente
Write-Host "Abriendo aplicación en el navegador..." -ForegroundColor Green
Start-Sleep -Seconds 2
Start-Process "http://localhost/EGRESAPP2"

return $functionalOk
