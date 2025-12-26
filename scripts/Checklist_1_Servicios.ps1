# ============================================================================
# CHECKLIST 1: VERIFICACIÓN DE SERVICIOS - EGRESAPP2
# ============================================================================

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CHECKLIST 1: VERIFICACIÓN DE SERVICIOS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$checks = @()

# 1.1 Apache corriendo
$apache = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
if ($apache) {
    Write-Host "✅ Apache está corriendo (PID: $($apache[0].Id))" -ForegroundColor Green
    $checks += $true
} else {
    Write-Host "❌ Apache NO está corriendo" -ForegroundColor Red
    $checks += $false
}

# 1.2 MySQL corriendo
$mysql = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysql) {
    Write-Host "✅ MySQL está corriendo (PID: $($mysql.Id))" -ForegroundColor Green
    $checks += $true
} else {
    Write-Host "❌ MySQL NO está corriendo" -ForegroundColor Red
    $checks += $false
}

# 1.3 Puerto 80 escuchando
$port80 = netstat -ano | Select-String ":80 " | Select-String "LISTENING"
if ($port80) {
    Write-Host "✅ Puerto 80 está en LISTEN" -ForegroundColor Green
    $checks += $true
} else {
    Write-Host "❌ Puerto 80 NO está escuchando" -ForegroundColor Red
    $checks += $false
}

# 1.4 Puerto 3306 escuchando
$port3306 = netstat -ano | Select-String ":3306 " | Select-String "LISTENING"
if ($port3306) {
    Write-Host "✅ Puerto 3306 está en LISTEN" -ForegroundColor Green
    $checks += $true
} else {
    Write-Host "❌ Puerto 3306 NO está escuchando" -ForegroundColor Red
    $checks += $false
}

# 1.5 PHP accesible
$phpPaths = @(
    "C:\laragon\bin\php\php-8.0.30\php.exe",
    "C:\laragon\bin\php\php-8.1.0\php.exe",
    "C:\xampp\php\php.exe"
)

$phpFound = $false
foreach ($path in $phpPaths) {
    if (Test-Path $path) {
        $phpVersion = & $path --version 2>&1 | Select-String "PHP (\d+\.\d+\.\d+)"
        if ($phpVersion) {
            Write-Host "✅ PHP instalado: $($phpVersion.Matches.Groups[1].Value)" -ForegroundColor Green
            $phpFound = $true
            $checks += $true
            break
        }
    }
}

if (-not $phpFound) {
    Write-Host "❌ PHP NO encontrado" -ForegroundColor Red
    $checks += $false
}

# Resultado
$passed = ($checks | Where-Object { $_ -eq $true }).Count
$total = $checks.Count
Write-Host ""
Write-Host "Resultado: $passed/$total checks pasados" -ForegroundColor $(if ($passed -eq $total) {"Green"} else {"Yellow"})

return ($passed -eq $total)
