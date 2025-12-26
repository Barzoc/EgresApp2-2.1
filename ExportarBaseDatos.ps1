# ============================================================================
# EXPORTADOR DE BASE DE DATOS - EGRESAPP2
# ============================================================================

$DbName = "gestion_egresados"
$DbUser = "root"
$DbPass = "" 
$OutputFile = "$PSScriptRoot\db\gestion_egresados_migracion.sql"

# Buscar mysqldump
$MysqlDumpPaths = @(
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe",
    "C:\xampp\mysql\bin\mysqldump.exe",
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
)

$MysqlDumpExe = $null
foreach ($path in $MysqlDumpPaths) {
    if (Test-Path $path) {
        $MysqlDumpExe = $path
        break
    }
}

if (-not $MysqlDumpExe) {
    Write-Host "Error: No se encontró mysqldump.exe" -ForegroundColor Red
    exit 1
}

Write-Host "Usando mysqldump: $MysqlDumpExe"
Write-Host "Exportando base de datos '$DbName'..."

$argsList = @("-u", $DbUser)
if ($DbPass) { $argsList += "-p$DbPass" }
$argsList += @("--databases", $DbName, "--routines", "--events", "--single-transaction", "--default-character-set=utf8mb4", "--result-file=$OutputFile")

$process = Start-Process -FilePath $MysqlDumpExe -ArgumentList $argsList -Wait -PassThru -NoNewWindow

if ($process.ExitCode -eq 0) {
    Write-Host "Base de datos exportada correctamente a: $OutputFile" -ForegroundColor Green
} else {
    Write-Host "Error al exportar la base de datos. Codigo: $($process.ExitCode)" -ForegroundColor Red
}

Write-Host "Presione cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
