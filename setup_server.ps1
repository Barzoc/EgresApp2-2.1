# Script para configurar MySQL en el Servidor Central
# Ejecutar en PowerShell como Administrador si es posible, o simplemente ejecutar.

$mysqlPath = "C:\laragon\bin\mysql\mysql-8.0.30\bin\mysql.exe"
if (-not (Test-Path $mysqlPath)) {
    # Buscar mysql dinamicamente si la version cambio
    $mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" | Select-Object -First 1 -ExpandProperty FullName
}

$setupSql = @"
-- Crear usuario remoto (cambia 'password_seguro' por lo que desees)
CREATE USER IF NOT EXISTS 'remoto'@'%' IDENTIFIED BY 'password_seguro';
GRANT ALL PRIVILEGES ON gestion_egresados.* TO 'remoto'@'%';
-- Tambien dar acceso a otras BD si es necesario
GRANT ALL PRIVILEGES ON *.* TO 'remoto'@'%'; 
FLUSH PRIVILEGES;

-- Configurar variables de auto-increment (para TABLAS que usen auto_increment de MySQL)
-- Central = impar (1, 3, 5...)
SET GLOBAL auto_increment_increment = 2;
SET GLOBAL auto_increment_offset = 1;

-- Verificar
SHOW VARIABLES LIKE 'auto_increment%';
"@

$sqlFile = "c:\laragon\www\EGRESAPP2\setup_server_db.sql"
$setupSql | Out-File -FilePath $sqlFile -Encoding UTF8

Write-Host "Ejecutando configuracion en MySQL..."
& $mysqlPath -u root -e "source $sqlFile"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Usuario 'remoto' creado y configuracion aplicada correctamente." -ForegroundColor Green
} else {
    Write-Host "Hubo un error configurando MySQL. Verifica si el servicio esta corriendo." -ForegroundColor Red
}

# Intentar abrir el archivo my.ini para que el usuario pueda editar bind-address
$myIni =Get-ChildItem "C:\laragon\bin\mysql\mysql-*\my.ini" | Select-Object -First 1 -ExpandProperty FullName
if ($myIni) {
    Write-Host "Abriendo $myIni para editar bind-address..."
    Write-Host "Busca la linea 'bind-address' y cambiala a: bind-address = 0.0.0.0" -ForegroundColor Cyan
    notepad $myIni
} else {
    Write-Host "No se encontro my.ini. Debes crearlo o buscarlo manualmente." -ForegroundColor Yellow
}
