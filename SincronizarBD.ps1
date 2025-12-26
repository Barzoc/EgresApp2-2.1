# Script de Sincronización (Cliente -> Servidor Central)
# Ejecutar este script EN EL PC CLIENTE (LOCAL)

# Configuración
$local_db = "gestion_egresados"
$local_user = "root"
$local_pass = "" # Usualmente vacío en Laragon local
$central_ip = "26.234.93.144" # IP Radmin VPN del Servidor
$central_user = "remoto"
$central_pass = 'Sistemas2025!' # Corregido con la nueva contraseña
$central_db = "gestion_egresados"

# Rutas de MySQL (Laragon)
$mysqlPath = "C:\laragon\bin\mysql\mysql-8.0.30\bin"
if (-not (Test-Path "$mysqlPath\mysql.exe")) {
    $mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin" | Select-Object -First 1 -ExpandProperty FullName
}
$mysqldump = "$mysqlPath\mysqldump.exe"
$mysql = "$mysqlPath\mysql.exe"

Write-Host "--- Iniciando Sincronización ---" -ForegroundColor Cyan
Write-Host "Modo: CLIENTE (Local) <--> SERVIDOR ($central_ip)"

# 1. PULL: Traer datos del Servidor Central (IDs < 1,000,000)
Write-Host "`n1. Descargando datos del Servidor Central..." -ForegroundColor Yellow
$dump_from_central = "c:\laragon\www\temp_pull_central.sql"

# Exportamos del central solo registros que pertenecen al central (IDs bajos)
# Usamos --no-create-info para no romper la estructura local
# Usamos REPLACE INTO o INSERT IGNORE para actualizar
# IMPORTANTE: Comillas alrededor del password para proteger caracteres especiales (como !)
# Usamos cmd /c para que la redirección > sea en formato correcto (ASCII/UTF-8) y no UTF-16 de PowerShell
# Usamos comillas simples para la condición SQL para evitar conflictos de escaping
cmd /c "$mysqldump -h $central_ip -u $central_user -p`"$central_pass`" --no-create-info --insert-ignore --where='identificacion < 1000000' $central_db egresado > $dump_from_central"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Descarga exitosa. Importando a local..."
    cmd /c "$mysql -u $local_user $local_db < $dump_from_central"
    Write-Host "Importación completada." -ForegroundColor Green
}
else {
    Write-Host "Error al conectar con servidor central. Verifica IP y Firewall." -ForegroundColor Red
    exit
}

# 2. PULL de Tablas Auxiliares (Tablas pequeñas que deben ser idénticas)
# Títulos, config, etc. Traemos novedades.
Write-Host "Sincronizando tablas auxiliares (Titulo, Config)..."
cmd /c "$mysqldump -h $central_ip -u $central_user -p`"$central_pass`" --no-create-info --insert-ignore $central_db titulo configuracion_certificado > c:\laragon\www\temp_aux.sql"
cmd /c "$mysql -u $local_user $local_db < c:\laragon\www\temp_aux.sql"


# 3. PUSH: Enviar mis datos locales (IDs >= 1,000,000) al Servidor Central
Write-Host "`n2. Subiendo datos locales al Servidor Central..." -ForegroundColor Yellow
$dump_local = "c:\laragon\www\temp_push_local.sql"

# Exportamos de local solo NUESTROS registros (IDs altos)
# Usamos comillas simples también aquí
cmd /c "$mysqldump -u $local_user --no-create-info --insert-ignore --where='identificacion >= 1000000' $local_db egresado > $dump_local"

if ((Get-Item $dump_local).Length -gt 0) {
    Write-Host "Subiendo datos..."
    # Protegemos el password con comillas dentro de la string de comando
    cmd /c "$mysql -h $central_ip -u $central_user -p`"$central_pass`" $central_db < $dump_local"
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Subida completada exitosamente." -ForegroundColor Green
    }
    else {
        Write-Host "Error subiendo datos." -ForegroundColor Red
    }
}
else {
    Write-Host "No hay datos locales nuevos para subir."
}

# Limpieza
Remove-Item $dump_from_central -ErrorAction SilentlyContinue
Remove-Item "c:\laragon\www\temp_aux.sql" -ErrorAction SilentlyContinue
Remove-Item $dump_local -ErrorAction SilentlyContinue

Write-Host "`n--- Sincronización Finalizada ---" -ForegroundColor Cyan
