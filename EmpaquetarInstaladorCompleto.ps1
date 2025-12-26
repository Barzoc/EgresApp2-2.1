# ============================================================================
# EMPAQUETADOR DE INSTALADOR - EGRESAPP2
# ============================================================================
# Este script crea un paquete completo para distribución
# ============================================================================

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "   EMPAQUETANDO INSTALADOR COMPLETO" -ForegroundColor Yellow
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$ProjectRoot = $PSScriptRoot
$PackageName = "EGRESAPP2_Instalador_$(Get-Date -Format 'yyyyMMdd_HHmm')"
$PackageDir = "$env:TEMP\$PackageName"
$OutputZip = "$ProjectRoot\$PackageName.zip"

Write-Host "Creando paquete de distribución..." -ForegroundColor Yellow
Write-Host ""

# Crear directorio temporal
if (Test-Path $PackageDir) {
    Remove-Item $PackageDir -Recurse -Force
}
New-Item -ItemType Directory -Path $PackageDir -Force | Out-Null

# Archivos y carpetas a incluir
$itemsToInclude = @(
    "InstaladorMaestro.bat",
    "InstaladorMaestro.ps1",
    "InstalarLaragon.ps1",
    "InstalarDependencias.ps1",
    "ImportarBaseDatos.ps1",
    "EGRESAPP2_Launcher.ps1",
    "CrearAccesoDirecto.ps1",
    "INSTRUCCIONES_USUARIO.md",
    "assets",
    "certificados",
    "config",
    "controlador",
    "db",
    "docs",
    "installers",
    "lib",
    "modelo",
    "scripts",
    "services",
    "templates",
    "utils",
    "vendor",
    "vista",
    "worker",
    "composer.json",
    "composer.lock",
    "index.php",
    "validar.php"
)

Write-Host "Copiando archivos al paquete..." -ForegroundColor Cyan

foreach ($item in $itemsToInclude) {
    $sourcePath = Join-Path $ProjectRoot $item
    if (Test-Path $sourcePath) {
        $destPath = Join-Path $PackageDir $item
        
        if (Test-Path $sourcePath -PathType Container) {
            Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
            Write-Host "  Copiado: $item (carpeta)" -ForegroundColor Green
        }
        else {
            Copy-Item -Path $sourcePath -Destination $destPath -Force
            Write-Host "  Copiado: $item" -ForegroundColor Green
        }
    }
    else {
        Write-Host "  No encontrado: $item" -ForegroundColor Yellow
    }
}

# Crear archivo README en el paquete
$readmeLines = @(
    "============================================================================",
    "                  INSTALADOR AUTOMATICO - EGRESAPP2",
    "                  Sistema de Gestion de Egresados",
    "============================================================================",
    "",
    "INSTRUCCIONES DE INSTALACION",
    "============================================================================",
    "",
    "1. Hacer clic derecho en 'InstaladorMaestro.bat'",
    "2. Seleccionar 'Ejecutar como administrador'",
    "3. Esperar a que termine la instalacion (15-30 minutos)",
    "4. Hacer doble clic en el icono 'EGRESAPP2' del escritorio",
    "",
    "CREDENCIALES POR DEFECTO",
    "============================================================================",
    "",
    "Email:      admin@test.com",
    "Contraseña: admin123",
    "",
    "REQUISITOS DEL SISTEMA",
    "============================================================================",
    "",
    "- Windows 10/11 (64-bit)",
    "- 4 GB RAM minimo (8 GB recomendado)",
    "- 5 GB espacio libre en disco",
    "- Conexion a Internet (para la instalacion)",
    "",
    "SOPORTE",
    "============================================================================",
    "",
    "Para mas informacion, consulte:",
    "- INSTRUCCIONES_USUARIO.md",
    "",
    "Version: 2.0",
    "Fecha: $(Get-Date -Format 'yyyy-MM-dd')",
    ""
)

$readmeContent = $readmeLines -join "`r`n"
Set-Content -Path "$PackageDir\LEEME.txt" -Value $readmeContent -Encoding UTF8

Write-Host ""
Write-Host "Comprimiendo paquete..." -ForegroundColor Cyan

# Comprimir el paquete
try {
    Compress-Archive -Path "$PackageDir\*" -DestinationPath $OutputZip -Force
    Write-Host ""
    Write-Host "Paquete creado exitosamente!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ubicacion: $OutputZip" -ForegroundColor Cyan
    $sizeInMB = [math]::Round((Get-Item $OutputZip).Length / 1MB, 2)
    Write-Host "Tamaño: $sizeInMB MB" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Este archivo ZIP contiene todo lo necesario para instalar" -ForegroundColor White
    Write-Host "EGRESAPP2 en cualquier PC con Windows." -ForegroundColor White
    Write-Host ""
}
catch {
    Write-Host "Error al crear el paquete: $_" -ForegroundColor Red
}

# Limpiar directorio temporal
Remove-Item $PackageDir -Recurse -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Presione cualquier tecla para continuar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
