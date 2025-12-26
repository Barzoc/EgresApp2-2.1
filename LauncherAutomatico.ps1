# ============================================================================
# LAUNCHER AUTOMÁTICO - EGRESAPP2
# ============================================================================
# Inicia todos los servicios y abre la aplicación automáticamente
# ============================================================================

param(
    [switch]$Silent,
    [switch]$NoWait
)

# Configuración de colores (solo si no es silent)
if (-not $Silent) {
    $Host.UI.RawUI.BackgroundColor = "Black"
    $Host.UI.RawUI.ForegroundColor = "White"
    Clear-Host
}

# Variables
$LaragonPath = "C:\laragon\laragon.exe"
$LaragonDir = "C:\laragon"
$AppURL = "http://localhost/EGRESAPP2"
$MaxWaitTime = 60 # segundos

# ============================================================================
# FUNCIONES
# ============================================================================

function Write-Status {
    param(
        [string]$Message,
        [string]$Type = "INFO"
    )
    
    if ($Silent) { return }
    
    switch ($Type) {
        "SUCCESS" { Write-Host "✓ $Message" -ForegroundColor Green }
        "ERROR" { Write-Host "✗ $Message" -ForegroundColor Red }
        "WARNING" { Write-Host "⚠ $Message" -ForegroundColor Yellow }
        "INFO" { Write-Host "ℹ $Message" -ForegroundColor Cyan }
        "STEP" { Write-Host "`n═══ $Message ═══" -ForegroundColor Magenta }
        default { Write-Host "  $Message" -ForegroundColor White }
    }
}

function Test-ServiceRunning {
    param([string]$ProcessName)
    $process = Get-Process -Name $ProcessName -ErrorAction SilentlyContinue
    return ($null -ne $process)
}

function Wait-ForService {
    param(
        [string]$ProcessName,
        [string]$ServiceName,
        [int]$TimeoutSeconds = 30
    )
    
    Write-Status "Esperando a que $ServiceName esté listo..." "INFO"
    
    $elapsed = 0
    while ($elapsed -lt $TimeoutSeconds) {
        if (Test-ServiceRunning -ProcessName $ProcessName) {
            Write-Status "$ServiceName está listo" "SUCCESS"
            return $true
        }
        
        Start-Sleep -Seconds 2
        $elapsed += 2
        
        if (-not $Silent) {
            Write-Host "." -NoNewline -ForegroundColor Yellow
        }
    }
    
    Write-Host ""
    Write-Status "$ServiceName no inició en $TimeoutSeconds segundos" "WARNING"
    return $false
}

function Test-DatabaseConnection {
    $mysqlPaths = @(
        "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe",
        "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
    )
    
    foreach ($path in $mysqlPaths) {
        if (Test-Path $path) {
            try {
                $result = & $path -u root -e "SELECT 1;" 2>&1
                if ($LASTEXITCODE -eq 0) {
                    return $true
                }
            } catch {
                # Continuar con siguiente ruta
            }
        }
    }
    
    return $false
}

# ============================================================================
# INICIO DEL LAUNCHER
# ============================================================================

if (-not $Silent) {
    Write-Host ""
    Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║                                                       ║" -ForegroundColor Cyan
    Write-Host "║         🚀 LAUNCHER AUTOMÁTICO - EGRESAPP2 🚀         ║" -ForegroundColor Yellow
    Write-Host "║                                                       ║" -ForegroundColor Cyan
    Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
    Write-Host ""
}

# ============================================================================
# PASO 1: VERIFICAR INSTALACIÓN DE LARAGON
# ============================================================================

Write-Status "PASO 1/5: Verificando instalación de Laragon" "STEP"

if (-not (Test-Path $LaragonPath)) {
    Write-Status "Laragon no encontrado en $LaragonPath" "ERROR"
    Write-Host ""
    Write-Host "Por favor, instale Laragon o ajuste la ruta en el script." -ForegroundColor Red
    Write-Host ""
    if (-not $NoWait) {
        Write-Host "Presione cualquier tecla para salir..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    }
    exit 1
}

Write-Status "Laragon encontrado" "SUCCESS"

# ============================================================================
# PASO 2: VERIFICAR/INICIAR SERVICIOS
# ============================================================================

Write-Status "PASO 2/5: Verificando servicios" "STEP"

$apacheRunning = Test-ServiceRunning -ProcessName "httpd"
$mysqlRunning = Test-ServiceRunning -ProcessName "mysqld"

if ($apacheRunning -and $mysqlRunning) {
    Write-Status "Apache y MySQL ya están corriendo" "SUCCESS"
} else {
    Write-Status "Iniciando Laragon..." "INFO"
    
    # Iniciar Laragon
    Start-Process -FilePath $LaragonPath -WorkingDirectory $LaragonDir
    Start-Sleep -Seconds 3
    
    # Intentar iniciar servicios con Laragon CLI
    $laragonCLI = "C:\laragon\laragon.exe"
    
    if (-not $apacheRunning) {
        Write-Status "Iniciando Apache..." "INFO"
        # Laragon inicia automáticamente, solo esperamos
        $apacheStarted = Wait-ForService -ProcessName "httpd" -ServiceName "Apache" -TimeoutSeconds 30
        
        if (-not $apacheStarted) {
            Write-Status "Apache no pudo iniciarse automáticamente" "WARNING"
            Write-Status "Por favor, inicie Apache manualmente desde Laragon" "INFO"
        }
    } else {
        Write-Status "Apache ya está corriendo" "SUCCESS"
    }
    
    if (-not $mysqlRunning) {
        Write-Status "Iniciando MySQL..." "INFO"
        $mysqlStarted = Wait-ForService -ProcessName "mysqld" -ServiceName "MySQL" -TimeoutSeconds 30
        
        if (-not $mysqlStarted) {
            Write-Status "MySQL no pudo iniciarse automáticamente" "WARNING"
            Write-Status "Por favor, inicie MySQL manualmente desde Laragon" "INFO"
        }
    } else {
        Write-Status "MySQL ya está corriendo" "SUCCESS"
    }
}

# ============================================================================
# PASO 3: VERIFICAR CONEXIÓN A BASE DE DATOS
# ============================================================================

Write-Status "PASO 3/5: Verificando conexión a la base de datos" "STEP"

$dbConnected = $false
$dbRetries = 0
$maxDbRetries = 10

while (-not $dbConnected -and $dbRetries -lt $maxDbRetries) {
    if (Test-DatabaseConnection) {
        Write-Status "Conexión a MySQL exitosa" "SUCCESS"
        $dbConnected = $true
    } else {
        $dbRetries++
        if ($dbRetries -lt $maxDbRetries) {
            Write-Status "Reintentando conexión a MySQL... ($dbRetries/$maxDbRetries)" "INFO"
            Start-Sleep -Seconds 2
        }
    }
}

if (-not $dbConnected) {
    Write-Status "No se pudo conectar a MySQL" "ERROR"
    Write-Host ""
    Write-Host "Posibles soluciones:" -ForegroundColor Yellow
    Write-Host "  1. Abra Laragon manualmente y haga clic en 'Start All'" -ForegroundColor White
    Write-Host "  2. Verifique que MySQL esté instalado en Laragon" -ForegroundColor White
    Write-Host "  3. Revise los logs de MySQL en C:\laragon\data\" -ForegroundColor White
    Write-Host ""
    if (-not $NoWait) {
        Write-Host "Presione cualquier tecla para intentar abrir la aplicación de todos modos..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    }
}

# ============================================================================
# PASO 4: VERIFICAR ACCESO HTTP
# ============================================================================

Write-Status "PASO 4/5: Verificando servidor web" "STEP"

try {
    $response = Invoke-WebRequest -Uri "http://localhost/" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    if ($response.StatusCode -eq 200) {
        Write-Status "Servidor web respondiendo correctamente" "SUCCESS"
    }
} catch {
    Write-Status "Servidor web no responde en localhost" "WARNING"
    Write-Status "Apache puede estar iniciando, continuando de todos modos..." "INFO"
}

# ============================================================================
# PASO 5: ABRIR APLICACIÓN
# ============================================================================

Write-Status "PASO 5/5: Abriendo EGRESAPP2 en el navegador" "STEP"

Start-Sleep -Seconds 2

try {
    Start-Process $AppURL
    Write-Status "Aplicación abierta en: $AppURL" "SUCCESS"
} catch {
    Write-Status "Error al abrir navegador" "ERROR"
    Write-Host ""
    Write-Host "Abra manualmente: $AppURL" -ForegroundColor Yellow
}

# ============================================================================
# RESUMEN FINAL
# ============================================================================

if (-not $Silent) {
    Write-Host ""
    Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║                                                       ║" -ForegroundColor Cyan
    Write-Host "║              ✅ EGRESAPP2 INICIADO ✅                  ║" -ForegroundColor Green
    Write-Host "║                                                       ║" -ForegroundColor Cyan
    Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Estado de servicios:" -ForegroundColor Yellow
    Write-Host "  • Apache:  " -NoNewline -ForegroundColor White
    if (Test-ServiceRunning -ProcessName "httpd") {
        Write-Host "✓ Corriendo" -ForegroundColor Green
    } else {
        Write-Host "✗ Detenido" -ForegroundColor Red
    }
    
    Write-Host "  • MySQL:   " -NoNewline -ForegroundColor White
    if (Test-ServiceRunning -ProcessName "mysqld") {
        Write-Host "✓ Corriendo" -ForegroundColor Green
    } else {
        Write-Host "✗ Detenido" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "URL de la aplicación:" -ForegroundColor Yellow
    Write-Host "  $AppURL" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Credenciales por defecto:" -ForegroundColor Yellow
    Write-Host "  Email:      admin@test.com" -ForegroundColor White
    Write-Host "  Contraseña: admin123" -ForegroundColor White
    Write-Host ""
    
    if (-not $NoWait) {
        Write-Host "Presione cualquier tecla para cerrar este launcher..." -ForegroundColor Gray
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    }
}

exit 0
