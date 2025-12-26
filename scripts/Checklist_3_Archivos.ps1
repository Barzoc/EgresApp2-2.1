# ============================================================================
# CHECKLIST 3: ARCHIVOS Y PERMISOS - EGRESAPP2
# ============================================================================

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CHECKLIST 3: ARCHIVOS Y PERMISOS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$projectRoot = "C:\laragon\www\EGRESAPP2"

# 3.1 Archivos core existen
$coreFiles = @(
    "$projectRoot\index.php",
    "$projectRoot\modelo\Conexion.php",
    "$projectRoot\composer.json"
)

$filesOk = $true
foreach ($file in $coreFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $(Split-Path $file -Leaf) presente" -ForegroundColor Green
    } else {
        Write-Host "❌ $(Split-Path $file -Leaf) FALTANTE" -ForegroundColor Red
        $filesOk = $false
    }
}

# 3.2 Vendor existe
$vendorPath = "$projectRoot\vendor\autoload.php"
if (Test-Path $vendorPath) {
    Write-Host "✅ vendor/autoload.php presente" -ForegroundColor Green
} else {
    Write-Host "❌ vendor/ FALTANTE (ejecutar composer install)" -ForegroundColor Red
    $filesOk = $false
}

# 3.3 Directorios con permisos de escritura
$writableDirs = @(
    "$projectRoot\certificados",
    "$projectRoot\temp",
    "$projectRoot\tmp",
    "$projectRoot\assets\expedientes\expedientes_subidos"
)

foreach ($dir in $writableDirs) {
    if (Test-Path $dir) {
        # Test escritura
        $testFile = "$dir\.write_test.tmp"
        try {
            "test" | Out-File $testFile -Force
            Remove-Item $testFile -Force
            Write-Host "✅ $(Split-Path $dir -Leaf): permisos OK" -ForegroundColor Green
        } catch {
            Write-Host "❌ $(Split-Path $dir -Leaf): SIN PERMISOS" -ForegroundColor Red
            $filesOk = $false
        }
    } else {
        Write-Host "❌ $(Split-Path $dir -Leaf): NO EXISTE" -ForegroundColor Red
        $filesOk = $false
    }
}

# 3.4 Vendor instalado correctamente
$vendorDir = "$projectRoot\vendor"
if (Test-Path $vendorDir) {
    $vendorSize = (Get-ChildItem $vendorDir -Recurse -ErrorAction SilentlyContinue | 
        Measure-Object -Property Length -Sum).Sum / 1MB
    
    if ($vendorSize -gt 10) {
        Write-Host "✅ Dependencias Composer: $([math]::Round($vendorSize, 1)) MB" -ForegroundColor Green
    } else {
        Write-Host "❌ Dependencias Composer INCOMPLETAS" -ForegroundColor Red
        $filesOk = $false
    }
} else {
    Write-Host "❌ Directorio vendor NO EXISTE" -ForegroundColor Red
    $filesOk = $false
}

return $filesOk
