# DebugConexion.ps1
$central_ip = "26.234.93.144"
$central_user = "remoto"
# Usamos comillas simples para que PowerShell NO intente interpretar el signo !
$central_pass = 'Sistemas2025!'

$mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" | Select-Object -First 1 -ExpandProperty FullName
if (-not $mysqlPath) {
    Write-Host "MySQL NO encontrado." -ForegroundColor Red
    exit
}

Write-Host "Probando conexion a $central_ip..."

# Construimos la lista de argumentos como un array para evitar problemas de comillas
$args = @(
    "-h", "$central_ip",
    "-u", "$central_user",
    "-p$central_pass",
    "-e", "SELECT VERSION();"
)

try {
    # Usamos Start-Process que maneja mejor los argumentos externos
    $proc = Start-Process -FilePath $mysqlPath -ArgumentList $args -NoNewWindow -Wait -PassThru
    
    if ($proc.ExitCode -eq 0) {
        Write-Host "`n[EXITO] La conexion fue EXITOSA." -ForegroundColor Green
    } else {
        Write-Host "`n[FALLO] La conexion fallo con codigo de salida: $($proc.ExitCode)" -ForegroundColor Red
        Write-Host "Revisa si la IP es correcta y si el servidor MySQL permite la conexion."
    }
} catch {
    Write-Host "Error Fatal: $_" -ForegroundColor Red
}

Write-Host "`nPresiona Enter para salir..."
Read-Host
