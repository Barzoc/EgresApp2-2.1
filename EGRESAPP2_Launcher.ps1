# ============================================================================
# LANZADOR DE EGRESAPP2 - Un Click para Iniciar Todo
# ============================================================================
# Este script inicia Laragon y abre la aplicación automáticamente
# Diseñado para ejecutarse desde un acceso directo del escritorio
# ============================================================================

# Configuración para ejecución silenciosa
$ErrorActionPreference = "SilentlyContinue"

# Rutas
$LaragonPath = "C:\laragon\laragon.exe"
$AppURL = "http://localhost/EGRESAPP2/index.php"

# Función para verificar si un proceso está corriendo
function Test-ProcessRunning {
    param($ProcessName)
    return (Get-Process -Name $ProcessName -ErrorAction SilentlyContinue) -ne $null
}

# Función para esperar a que un servicio esté listo
function Wait-ServiceReady {
    param($ProcessName, $MaxWait = 30)
    
    $waited = 0
    while ($waited -lt $MaxWait) {
        if (Test-ProcessRunning $ProcessName) {
            return $true
        }
        Start-Sleep -Seconds 1
        $waited++
    }
    return $false
}

# ============================================================================
# INICIO DEL PROCESO
# ============================================================================

# Verificar si Laragon está instalado
if (-not (Test-Path $LaragonPath)) {
    [System.Windows.Forms.MessageBox]::Show(
        "Laragon no está instalado en C:\laragon`n`nPor favor, ejecute el instalador primero.",
        "EGRESAPP2 - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    )
    exit 1
}

# Verificar si Laragon ya está corriendo
if (-not (Test-ProcessRunning "laragon")) {
    # Iniciar Laragon
    Start-Process $LaragonPath -WindowStyle Hidden
    Start-Sleep -Seconds 3
}

# Iniciar servicios de Laragon
Start-Process $LaragonPath -ArgumentList "start" -WindowStyle Hidden -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Esperar a que Apache esté listo
$apacheReady = Wait-ServiceReady "httpd" 15

# Esperar a que MySQL esté listo
$mysqlReady = Wait-ServiceReady "mysqld" 15

# Verificar si los servicios están corriendo
if ($apacheReady -and $mysqlReady) {
    # Todo listo, abrir navegador
    Start-Sleep -Seconds 2
    Start-Process $AppURL
}
elseif ($apacheReady) {
    # Solo Apache está corriendo, intentar abrir de todos modos
    Start-Sleep -Seconds 2
    Start-Process $AppURL
}
else {
    # Los servicios no iniciaron, mostrar mensaje
    Add-Type -AssemblyName System.Windows.Forms
    [System.Windows.Forms.MessageBox]::Show(
        "Los servicios de Laragon no se iniciaron correctamente.`n`nPor favor, inicie Laragon manualmente y haga clic en 'Start All'.",
        "EGRESAPP2 - Advertencia",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Warning
    )
}

# Salir silenciosamente
exit 0
