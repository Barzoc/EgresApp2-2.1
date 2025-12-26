# VerificarLocal.ps1
# Buscar MySQL
$mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" | Select-Object -First 1 -ExpandProperty FullName
if (-not $mysqlPath) {
    Write-Host "MySQL NO encontrado." -ForegroundColor Red
    exit
}

Write-Host "Consultando base de datos LOCAL (gestion_egresados)..."

# Usamos cmd /c para evitar problemas de parsing de PowerShell
$query = "SELECT COUNT(*) as 'Total Locales', MAX(identificacion) as 'Max ID' FROM egresado;"
$cmd = "& `"$mysqlPath`" -u root gestion_egresados -e `"$query`""

# Ejecutar
Invoke-Expression $cmd

Write-Host "`nUltimos 5 registros:"
$query2 = "SELECT identificacion, nombreCompleto FROM egresado ORDER BY identificacion DESC LIMIT 5;"
$cmd2 = "& `"$mysqlPath`" -u root gestion_egresados -e `"$query2`""
Invoke-Expression $cmd2
