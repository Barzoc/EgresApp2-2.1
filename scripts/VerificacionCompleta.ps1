# ============================================================================
# VERIFICACIÓN COMPLETA POST-MIGRACIÓN - EGRESAPP2
# ============================================================================
# Ejecuta todos los checklists y genera un reporte completo
# ============================================================================

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "║   VERIFICACIÓN COMPLETA POST-MIGRACIÓN - EGRESAPP2    ║" -ForegroundColor Yellow
Write-Host "║                                                       ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$results = @()
$checklistNames = @(
    "Servicios (Apache, MySQL, Puertos)",
    "Base de Datos (Estructura, Datos)",
    "Archivos y Permisos",
    "Herramientas Externas",
    "Verificación Funcional"
)

# Ejecutar Checklist 1
$results += & "$PSScriptRoot\Checklist_1_Servicios.ps1"
Write-Host ""
Start-Sleep -Seconds 2

# Ejecutar Checklist 2
$results += & "$PSScriptRoot\Checklist_2_BaseDatos.ps1"
Write-Host ""
Start-Sleep -Seconds 2

# Ejecutar Checklist 3
$results += & "$PSScriptRoot\Checklist_3_Archivos.ps1"
Write-Host ""
Start-Sleep -Seconds 2

# Ejecutar Checklist 4
$results += & "$PSScriptRoot\Checklist_4_Herramientas.ps1"
Write-Host ""
Start-Sleep -Seconds 2

# Ejecutar Checklist 5
$results += & "$PSScriptRoot\Checklist_5_Funcional.ps1"
Write-Host ""

# ============================================================================
# RESUMEN FINAL
# ============================================================================
$passed = ($results | Where-Object { $_ -eq $true }).Count
$total = $results.Count

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                 RESUMEN FINAL                         ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Mostrar resultados detallados
for ($i = 0; $i -lt $results.Count; $i++) {
    $status = if ($results[$i]) {"✅ PASS"} else {"❌ FAIL"}
    $color = if ($results[$i]) {"Green"} else {"Red"}
    Write-Host "  $status - $($checklistNames[$i])" -ForegroundColor $color
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

if ($passed -eq $total) {
    Write-Host ""
    Write-Host "  ✅ MIGRACIÓN EXITOSA" -ForegroundColor Green
    Write-Host "  Todos los checks pasaron ($passed/$total)" -ForegroundColor Green
    Write-Host ""
    Write-Host "  La aplicación EGRESAPP2 está lista para uso." -ForegroundColor White
    Write-Host ""
} elseif ($passed -ge ($total - 1)) {
    Write-Host ""
    Write-Host "  ⚠️ MIGRACIÓN CASI COMPLETA" -ForegroundColor Yellow
    Write-Host "  Checks pasados: $passed/$total" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  La aplicación puede funcionar, pero hay alertas." -ForegroundColor White
    Write-Host "  Revise los errores anteriores." -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "  ❌ MIGRACIÓN INCOMPLETA" -ForegroundColor Red
    Write-Host "  Checks pasados: $passed/$total" -ForegroundColor Red
    Write-Host ""
    Write-Host "  Corrija los errores antes de usar la aplicación." -ForegroundColor White
    Write-Host ""
}

# Generar reporte en archivo
$reportPath = "$PSScriptRoot\..\verificacion_$(Get-Date -Format 'yyyyMMdd_HHmmss').txt"
$report = @"
REPORTE DE VERIFICACIÓN POST-MIGRACIÓN - EGRESAPP2
═══════════════════════════════════════════════════

Fecha: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Resultado: $passed/$total checks pasados

DETALLE DE VERIFICACIONES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

"@

for ($i = 0; $i -lt $results.Count; $i++) {
    $status = if ($results[$i]) {"[✓] PASS"} else {"[✗] FAIL"}
    $report += "`n$status - $($checklistNames[$i])"
}

$report += @"


RECOMENDACIONES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

$(if ($passed -eq $total) {
    "✓ Sistema completamente operativo`n✓ Se puede usar en producción"
} elseif ($passed -ge ($total - 1)) {
    "⚠ Revise los checks fallidos`n⚠ Sistema puede funcionar con limitaciones"
} else {
    "✗ NO usar en producción`n✗ Corrija todos los errores primero"
})

"@

$report | Out-File -FilePath $reportPath -Encoding UTF8
Write-Host "Reporte guardado en: $reportPath" -ForegroundColor Cyan

Write-Host ""
Write-Host "Presione cualquier tecla para salir..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
