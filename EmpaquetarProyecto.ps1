# ============================================================================
# EMPAQUETADOR DE PROYECTO - EGRESAPP2
# ============================================================================

$ProjectRoot = $PSScriptRoot
$Timestamp = Get-Date -Format "yyyyMMdd_HHmm"
$ZipName = "EGRESAPP2_Migracion_$Timestamp.zip"
$ZipPath = Join-Path $ProjectRoot $ZipName

# Archivos y carpetas a excluir
$Exclusions = @(
    "*.git*",
    "*.vscode*",
    "*.idea*",
    "*node_modules*",
    "*tmp\*",
    "*temp\*",
    "*.zip",
    "*.rar",
    "*.7z",
    "*storage\logs\*",
    "*tests\*",
    "*.specstory*"
)

Write-Host "EMPAQUETADOR DE PROYECTO - EGRESAPP2"
Write-Host "------------------------------------"

# 1. Verificar si existen instaladores
if (-not (Test-Path "$ProjectRoot\installers")) {
    Write-Host "ADVERTENCIA: No se encontró la carpeta 'installers'." -ForegroundColor Yellow
}

# 2. Verificar si existe dump de BD reciente
$sqlFile = "$ProjectRoot\db\gestion_egresados_migracion.sql"
if (-not (Test-Path $sqlFile)) {
    Write-Host "ADVERTENCIA: No se encontró el dump de base de datos." -ForegroundColor Yellow
}

Write-Host "Creando archivo ZIP: $ZipName"
Write-Host "Esto puede tomar unos minutos..."

# Obtener lista de archivos excluyendo patrones
$files = Get-ChildItem -Path $ProjectRoot -Recurse | Where-Object {
    $path = $_.FullName
    $exclude = $false
    foreach ($pattern in $Exclusions) {
        if ($path -like $pattern) {
            $exclude = $true
            break
        }
    }
    return -not $exclude
}

# Comprimir
Compress-Archive -Path $ProjectRoot -DestinationPath $ZipPath -Update -CompressionLevel Optimal

if (Test-Path $ZipPath) {
    Write-Host "Paquete creado exitosamente!" -ForegroundColor Green
    Write-Host "Ubicación: $ZipPath"
} else {
    Write-Host "Error al crear el paquete." -ForegroundColor Red
}

Write-Host "Presione cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
