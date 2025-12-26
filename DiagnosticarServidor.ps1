# DiagnosticarServidor.ps1
# Script completo para diagnosticar problemas de conexión al servidor MySQL central

param(
    [string]$central_ip = "26.234.93.144",
    [string]$central_user = "remoto",
    [string]$central_pass = 'Sistemas2025!',
    [int]$central_port = 3306
)

Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "      DIAGNÓSTICO DE CONEXIÓN AL SERVIDOR CENTRAL" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# 1. Test de Conectividad de Red (PING)
Write-Host "[1/5] Verificando conectividad de red (PING)..." -ForegroundColor Yellow
$ping = Test-Connection -ComputerName $central_ip -Count 2 -Quiet
if ($ping) {
    Write-Host "  ✓ PING exitoso al servidor $central_ip" -ForegroundColor Green
} else {
    Write-Host "  ✗ PING FALLÓ - El servidor no responde en la red" -ForegroundColor Red
    Write-Host "    Posibles causas:" -ForegroundColor Yellow
    Write-Host "    - VPN Radmin no está conectada" -ForegroundColor Yellow
    Write-Host "    - IP incorrecta" -ForegroundColor Yellow
    Write-Host "    - Servidor apagado" -ForegroundColor Yellow
    exit 1
}

# 2. Test de Puerto MySQL (3306)
Write-Host "`n[2/5] Verificando puerto MySQL ($central_port)..." -ForegroundColor Yellow
try {
    $tcpClient = New-Object System.Net.Sockets.TcpClient
    $asyncResult = $tcpClient.BeginConnect($central_ip, $central_port, $null, $null)
    $wait = $asyncResult.AsyncWaitHandle.WaitOne(3000, $false)
    
    if ($wait) {
        $tcpClient.EndConnect($asyncResult)
        $tcpClient.Close()
        Write-Host "  ✓ Puerto $central_port ESTÁ ABIERTO" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Puerto $central_port ESTÁ CERRADO o no responde" -ForegroundColor Red
        Write-Host "    Posibles causas:" -ForegroundColor Yellow
        Write-Host "    - Firewall de Windows bloqueando el puerto" -ForegroundColor Yellow
        Write-Host "    - MySQL no está escuchando en ese puerto" -ForegroundColor Yellow
        Write-Host "    - MySQL configurado solo para localhost" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ✗ Error verificando puerto: $_" -ForegroundColor Red
}

# 3. Verificar que MySQL local esté disponible
Write-Host "`n[3/5] Buscando cliente MySQL local..." -ForegroundColor Yellow
$mysqlPath = Get-ChildItem "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
if ($mysqlPath) {
    Write-Host "  ✓ MySQL encontrado: $mysqlPath" -ForegroundColor Green
} else {
    Write-Host "  ✗ MySQL NO encontrado en Laragon" -ForegroundColor Red
    exit 1
}

# 4. Test de Conexión MySQL
Write-Host "`n[4/5] Intentando conexión MySQL al servidor central..." -ForegroundColor Yellow
$args = @(
    "-h", "$central_ip",
    "-u", "$central_user",
    "-p$central_pass",
    "-e", "SELECT VERSION() AS 'Version MySQL', DATABASE() AS 'Base de Datos', USER() AS 'Usuario Conectado';"
)

try {
    $proc = Start-Process -FilePath $mysqlPath -ArgumentList $args -NoNewWindow -Wait -PassThru -RedirectStandardOutput "temp_mysql_output.txt" -RedirectStandardError "temp_mysql_error.txt"
    
    if ($proc.ExitCode -eq 0) {
        Write-Host "  ✓ CONEXIÓN MYSQL EXITOSA!" -ForegroundColor Green
        Write-Host "`n  Información del servidor:" -ForegroundColor Cyan
        Get-Content "temp_mysql_output.txt" | Write-Host
    } else {
        Write-Host "  ✗ CONEXIÓN MYSQL FALLÓ (Código de salida: $($proc.ExitCode))" -ForegroundColor Red
        
        $error_content = Get-Content "temp_mysql_error.txt" -Raw
        Write-Host "`n  Detalles del error:" -ForegroundColor Yellow
        Write-Host $error_content
        
        # Analizar errores comunes
        if ($error_content -match "ERROR 1045") {
            Write-Host "`n  Diagnóstico: CREDENCIALES INCORRECTAS" -ForegroundColor Red
            Write-Host "  - Verifica el usuario y contraseña" -ForegroundColor Yellow
        } elseif ($error_content -match "ERROR 2003") {
            Write-Host "`n  Diagnóstico: NO SE PUEDE CONECTAR AL SERVIDOR" -ForegroundColor Red
            Write-Host "  - MySQL no está configurado para aceptar conexiones remotas" -ForegroundColor Yellow
            Write-Host "  - Ejecuta el script ConfigurarServidorMySQL.ps1 EN EL SERVIDOR" -ForegroundColor Yellow
        } elseif ($error_content -match "ERROR 1130") {
            Write-Host "`n  Diagnóstico: HOST NO TIENE PERMISOS" -ForegroundColor Red
            Write-Host "  - El usuario '$central_user' no tiene permisos desde este host" -ForegroundColor Yellow
            Write-Host "  - Ejecuta en el servidor: GRANT ALL ON *.* TO '$central_user'@'%';" -ForegroundColor Yellow
        }
    }
    
    # Limpieza
    Remove-Item "temp_mysql_output.txt" -ErrorAction SilentlyContinue
    Remove-Item "temp_mysql_error.txt" -ErrorAction SilentlyContinue
    
} catch {
    Write-Host "  ✗ Error ejecutando MySQL: $_" -ForegroundColor Red
}

# 5. Test de Consulta a la Base de Datos
if ($proc.ExitCode -eq 0) {
    Write-Host "`n[5/5] Consultando tabla egresado..." -ForegroundColor Yellow
    $args = @(
        "-h", "$central_ip",
        "-u", "$central_user",
        "-p$central_pass",
        "gestion_egresados",
        "-e", "SELECT COUNT(*) AS 'Total Egresados', MAX(identificacion) AS 'Max ID' FROM egresado;"
    )
    
    Start-Process -FilePath $mysqlPath -ArgumentList $args -NoNewWindow -Wait
}

Write-Host "`n═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                 DIAGNÓSTICO COMPLETADO" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan

Write-Host "`nPresiona Enter para salir..."
Read-Host
