# ============================================================================
# CHECKLIST 4: HERRAMIENTAS EXTERNAS - EGRESAPP2
# ============================================================================

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CHECKLIST 4: HERRAMIENTAS EXTERNAS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$toolsOk = $true

# 4.1 Tesseract OCR
try {
    $tesseract = & tesseract --version 2>&1
    if ($tesseract -match "tesseract (\d+\.\d+)") {
        Write-Host "✅ Tesseract OCR $($matches[1]) instalado" -ForegroundColor Green
        
        # Verificar idioma español
        $tessdata = & tesseract --list-langs 2>&1
        if ($tessdata -match "spa") {
            Write-Host "  ✅ Idioma español (spa) disponible" -ForegroundColor Green
        } else {
            Write-Host "  ⚠️ Idioma español (spa) NO disponible" -ForegroundColor Yellow
        }
    } else {
        Write-Host "❌ Tesseract OCR NO encontrado" -ForegroundColor Red
        $toolsOk = $false
    }
} catch {
    Write-Host "❌ Tesseract OCR NO encontrado" -ForegroundColor Red
    $toolsOk = $false
}

# 4.2 ImageMagick
try {
    $convert = & convert --version 2>&1
    if ($convert -match "Version: ImageMagick (\S+)") {
        Write-Host "✅ ImageMagick $($matches[1]) instalado" -ForegroundColor Green
    } else {
        Write-Host "❌ ImageMagick NO encontrado" -ForegroundColor Red
        $toolsOk = $false
    }
} catch {
    Write-Host "❌ ImageMagick NO encontrado" -ForegroundColor Red
    $toolsOk = $false
}

# 4.3 LibreOffice
$soffice = "C:\Program Files\LibreOffice\program\soffice.exe"
if (Test-Path $soffice) {
    Write-Host "✅ LibreOffice instalado" -ForegroundColor Green
} else {
    Write-Host "❌ LibreOffice NO encontrado" -ForegroundColor Red
    $toolsOk = $false
}

# 4.4 Composer
try {
    $composer = & composer --version 2>&1
    if ($composer -match "Composer version (\S+)") {
        Write-Host "✅ Composer $($matches[1]) instalado" -ForegroundColor Green
    } else {
        Write-Host "❌ Composer NO encontrado" -ForegroundColor Red
        $toolsOk = $false
    }
} catch {
    Write-Host "❌ Composer NO encontrado" -ForegroundColor Red
    $toolsOk = $false
}

return $toolsOk
