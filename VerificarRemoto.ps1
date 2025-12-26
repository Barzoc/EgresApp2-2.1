# VerificarRemoto.ps1
$central_ip = "26.234.93.144"
$central_user = "remoto"
$central_pass = 'Sistemas2025!'
$central_db = "gestion_egresados"

$mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" | Select-Object -First 1 -ExpandProperty FullName
if (-not $mysqlPath) {
    Write-Host "MySQL NO encontrado." -ForegroundColor Red
    exit
}

Write-Host "Contando registros en SERVIDOR REMOTO ($central_ip)..."

# Usamos cmd /c para evitar problemas de parsing de PowerShell
$query = "SELECT COUNT(*) as 'Total Remotos', MAX(identificacion) as 'Max ID' FROM egresado;"
$cmd = "& `"$mysqlPath`" -h $central_ip -u $central_user -p`"$central_pass`" $central_db -e `"$query`""

# Ejecutar
Invoke-Expression $cmd
