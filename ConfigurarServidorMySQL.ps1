# ConfigurarServidorMySQL.ps1
# EJECUTAR ESTE SCRIPT EN EL SERVIDOR CENTRAL (PC Principal)
# Configura MySQL para aceptar conexiones remotas

# Verificar permisos de Administrador
if (!([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "Este script DEBE ejecutarse como Administrador" -ForegroundColor Red
    Write-Host "Haz clic derecho en el script y selecciona 'Ejecutar como administrador'" -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  CONFIGURACIÓN DEL SERVIDOR MYSQL PARA ACCESO REMOTO" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# 1. Localizar MySQL
Write-Host "[1/5] Localizando MySQL en Laragon..." -ForegroundColor Yellow
$mysqlBinPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
$mysqlDataPath = Get-ChildItem "C:\laragon\data\mysql" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName

if (-not $mysqlBinPath) {
    Write-Host "  ✗ MySQL NO encontrado en Laragon" -ForegroundColor Red
    exit 1
}

$mysqlPath = "$mysqlBinPath\mysql.exe"
$my_ini = "C:\laragon\bin\mysql\" + (Split-Path $mysqlBinPath -Parent | Split-Path -Leaf) + "\my.ini"

Write-Host "  ✓ MySQL encontrado: $mysqlPath" -ForegroundColor Green
Write-Host "  ✓ Archivo de configuración: $my_ini" -ForegroundColor Green

# 2. Modificar my.ini para permitir conexiones remotas
Write-Host "`n[2/5] Configurando my.ini para aceptar conexiones remotas..." -ForegroundColor Yellow

if (Test-Path $my_ini) {
    # Hacer backup
    $backup = $my_ini + ".backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")
    Copy-Item $my_ini $backup
    Write-Host "  ✓ Backup creado: $backup" -ForegroundColor Green
    
    # Leer contenido
    $content = Get-Content $my_ini
    
    # Buscar y comentar bind-address si existe
    $modified = $false
    $newContent = @()
    foreach ($line in $content) {
        if ($line -match "^\s*bind-address\s*=") {
            $newContent += "# $line (comentado por ConfigurarServidorMySQL.ps1)"
            $modified = $true
            Write-Host "  ✓ Comentado: $line" -ForegroundColor Green
        } else {
            $newContent += $line
        }
    }
    
    # Agregar bind-address = 0.0.0.0 en la sección [mysqld]
    $finalContent = @()
    $inMysqld = $false
    $alreadyAdded = $false
    
    foreach ($line in $newContent) {
        $finalContent += $line
        
        if ($line -match "^\[mysqld\]") {
            $inMysqld = $true
        } elseif ($line -match "^\[") {
            $inMysqld = $false
        }
        
        if ($inMysqld -and -not $alreadyAdded) {
            $finalContent += "# Permitir conexiones remotas (agregado por ConfigurarServidorMySQL.ps1)"
            $finalContent += "bind-address = 0.0.0.0"
            $alreadyAdded = $true
            Write-Host "  ✓ Agregado: bind-address = 0.0.0.0" -ForegroundColor Green
        }
    }
    
    # Guardar cambios
    $finalContent | Out-File -FilePath $my_ini -Encoding UTF8
    Write-Host "  ✓ Configuración guardada" -ForegroundColor Green
    
} else {
    Write-Host "  ✗ No se encontró my.ini en $my_ini" -ForegroundColor Red
    Write-Host "  Búscalo manualmente y agrega la línea: bind-address = 0.0.0.0" -ForegroundColor Yellow
}

# 3. Configurar Firewall de Windows
Write-Host "`n[3/5] Configurando Firewall de Windows (puerto 3306)..." -ForegroundColor Yellow

try {
    # Verificar si la regla ya existe
    $existingRule = Get-NetFirewallRule -DisplayName "MySQL Server (Port 3306)" -ErrorAction SilentlyContinue
    
    if ($existingRule) {
        Write-Host "  ⚠ La regla de firewall ya existe. Eliminando..." -ForegroundColor Yellow
        Remove-NetFirewallRule -DisplayName "MySQL Server (Port 3306)"
    }
    
    # Crear nueva regla
    New-NetFirewallRule -DisplayName "MySQL Server (Port 3306)" `
                        -Direction Inbound `
                        -Protocol TCP `
                        -LocalPort 3306 `
                        -Action Allow `
                        -Profile Any `
                        -Description "Permite conexiones MySQL remotas para EGRESAPP2" | Out-Null
    
    Write-Host "  ✓ Regla de firewall creada exitosamente" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Error configurando firewall: $_" -ForegroundColor Red
}

# 4. Crear usuario remoto en MySQL
Write-Host "`n[4/5] Creando usuario remoto en MySQL..." -ForegroundColor Yellow

$usuario = "remoto"
$password = "Sistemas2025!"

$sql = @"
-- Eliminar usuario si existe
DROP USER IF EXISTS '$usuario'@'%';

-- Crear usuario con acceso desde cualquier host
CREATE USER '$usuario'@'%' IDENTIFIED BY '$password';

-- Otorgar todos los privilegios en gestion_egresados
GRANT ALL PRIVILEGES ON gestion_egresados.* TO '$usuario'@'%';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar
SELECT User, Host FROM mysql.user WHERE User='$usuario';
"@

# Ejecutar SQL
$sql | & $mysqlPath -u root --execute="source /dev/stdin"

if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ Usuario '$usuario' creado con éxito" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Advertencia: Posible error al crear usuario" -ForegroundColor Yellow
    Write-Host "    Intenta manualmente:" -ForegroundColor Yellow
    Write-Host "    CREATE USER '$usuario'@'%' IDENTIFIED BY '$password';" -ForegroundColor Gray
    Write-Host "    GRANT ALL PRIVILEGES ON gestion_egresados.* TO '$usuario'@'%';" -ForegroundColor Gray
    Write-Host "    FLUSH PRIVILEGES;" -ForegroundColor Gray
}

# 5. Reiniciar MySQL
Write-Host "`n[5/5] Reiniciando servicio MySQL..." -ForegroundColor Yellow

# Buscar servicio MySQL
$mysqlService = Get-Service | Where-Object { $_.DisplayName -like "*mysql*" -or $_.Name -like "*mysql*" } | Select-Object -First 1

if ($mysqlService) {
    Write-Host "  ✓ Servicio encontrado: $($mysqlService.DisplayName)" -ForegroundColor Green
    
    try {
        Restart-Service $mysqlService.Name -Force
        Write-Host "  ✓ Servicio MySQL reiniciado exitosamente" -ForegroundColor Green
    } catch {
        Write-Host "  ⚠ No se pudo reiniciar automáticamente: $_" -ForegroundColor Yellow
        Write-Host "    Reinicia MySQL manualmente desde el Panel de Laragon" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ⚠ No se encontró servicio MySQL" -ForegroundColor Yellow
    Write-Host "    Reinicia MySQL manualmente desde el Panel de Laragon" -ForegroundColor Yellow
}

# Resumen
Write-Host "`n═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                  CONFIGURACIÓN COMPLETADA" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan

Write-Host "`nCambios realizados:" -ForegroundColor Green
Write-Host " ✓ my.ini configurado con bind-address = 0.0.0.0" -ForegroundColor White
Write-Host " ✓ Regla de firewall creada (puerto 3306)" -ForegroundColor White
Write-Host " ✓ Usuario remoto creado: $usuario" -ForegroundColor White
Write-Host " ✓ Servicio MySQL reiniciado" -ForegroundColor White

Write-Host "`nDatos de conexión para clientes:" -ForegroundColor Cyan
Write-Host " Host: [IP de este servidor en VPN]" -ForegroundColor White
Write-Host " Puerto: 3306" -ForegroundColor White
Write-Host " Usuario: $usuario" -ForegroundColor White
Write-Host " Contraseña: $password" -ForegroundColor White
Write-Host " Base de datos: gestion_egresados" -ForegroundColor White

Write-Host "`n⚠️ IMPORTANTE: Anota la IP de Radmin VPN de este servidor" -ForegroundColor Yellow
Write-Host "   para configurarla en los clientes." -ForegroundColor Yellow

Write-Host "`nPresiona Enter para salir..."
Read-Host
