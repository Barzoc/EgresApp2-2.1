@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   CONFIGURACIÓN SERVIDOR MYSQL CENTRAL
echo   EGRESAPP2 - Acceso por Internet
echo ========================================
echo.

REM Verificar privilegios de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ❌ ERROR: Este script requiere privilegios de Administrador
    echo.
    echo Por favor:
    echo 1. Click derecho en este archivo
    echo 2. Selecciona "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo [✓] Ejecutando como Administrador
echo.

echo ========================================
echo PASO 1: Detectar IP del Servidor
echo ========================================
echo.

REM Obtener IP local
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
    set LOCAL_IP=%%a
    goto :ip_found
)

:ip_found
set LOCAL_IP=%LOCAL_IP:~1%
echo 📍 IP Local del servidor: %LOCAL_IP%
echo.

echo ⚠️  También necesitas tu IP PÚBLICA para acceso por Internet:
echo.
echo   1. Abre en tu navegador: https://www.whatismyip.com
echo   2. Anota tu IP pública (ejemplo: 200.123.45.67)
echo   3. O configura un dominio DynDNS (recomendado)
echo.
pause

echo.
echo ========================================
echo PASO 2: Configurar Firewall
echo ========================================
echo.

echo Permitiendo MySQL (puerto 3306) en firewall...

REM Verificar si la regla ya existe
netsh advfirewall firewall show rule name="MySQL Server Remote Access" >nul 2>&1
if %errorLevel% equ 0 (
    echo ⚠️  Regla ya existe, eliminando versión anterior...
    netsh advfirewall firewall delete rule name="MySQL Server Remote Access" >nul
)

REM Crear nueva regla
netsh advfirewall firewall add rule ^
    name="MySQL Server Remote Access" ^
    dir=in ^
    action=allow ^
    protocol=TCP ^
    localport=3306 ^
    description="Permite acceso remoto a MySQL para EGRESAPP2"

if %errorLevel% equ 0 (
    echo [✓] Firewall configurado correctamente
) else (
    echo [❌] Error al configurar firewall
    pause
    exit /b 1
)

echo.
echo ========================================
echo PASO 3: Verificar MySQL
echo ========================================
echo.

REM Buscar instalación de MySQL en Laragon
set MYSQL_BIN=
set MYSQL_INI=

if exist "C:\laragon\bin\mysql\" (
    for /d %%i in (C:\laragon\bin\mysql\*) do (
        if exist "%%i\bin\mysql.exe" (
            set MYSQL_BIN=%%i\bin
            if exist "%%i\my.ini" set MYSQL_INI=%%i\my.ini
        )
    )
)

if "%MYSQL_BIN%"=="" (
    echo [⚠️ ] MySQL no encontrado en ubicación estándar de Laragon
    echo.
    echo Por favor configura manualmente:
    echo 1. Archivo my.ini
    echo 2. Reinicia MySQL
    goto :manual_config
)

echo [✓] MySQL encontrado: %MYSQL_BIN%
echo.

if "%MYSQL_INI%"=="" (
    echo [⚠️ ] Archivo my.ini no encontrado automáticamente
    goto :manual_config
)

echo [✓] Archivo my.ini encontrado: %MYSQL_INI%
echo.

echo IMPORTANTE - Configuración Manual Requerida:
echo.
echo 1. Abre el archivo: %MYSQL_INI%
echo 2. Busca la línea: bind-address = 127.0.0.1
echo 3. Cámbiala por: bind-address = 0.0.0.0
echo 4. Guarda el archivo
echo 5. Reinicia MySQL desde Laragon
echo.
echo ¿Quieres abrir el archivo ahora? (S/N)
set /p OPEN_INI=

if /i "%OPEN_INI%"=="S" (
    notepad "%MYSQL_INI%"
)

:manual_config

echo.
echo ========================================
echo PASO 4: Configurar Router
echo ========================================
echo.

echo ⚠️  IMPORTANTE - Configuración MANUAL en tu Router:
echo.
echo 1. Accede a tu router (usualmente 192.168.1.1 o 192.168.0.1)
echo 2. Busca "Port Forwarding" o "Virtual Server"
echo 3. Crea nueva regla:
echo    - Nombre: MySQL EGRESAPP2
echo    - Puerto Externo: 3306
echo    - Puerto Interno: 3306
echo    - IP Interna: %LOCAL_IP%
echo    - Protocolo: TCP
echo 4. Guarda los cambios
echo.

pause

echo.
echo ========================================
echo PASO 5: Configurar DynDNS (Opcional)
echo ========================================
echo.

echo Para evitar problemas con IP dinámica, se recomienda usar DynDNS:
echo.
echo Servicios gratuitos:
echo   • No-IP: https://www.noip.com
echo   • DuckDNS: https://www.duckdns.org
echo   • Dynu: https://www.dynu.com
echo.
echo Después de crear tu cuenta:
echo 1. Crea un hostname (ejemplo: mi-egresapp.ddns.net)
echo 2. Descarga e instala el cliente
echo 3. El cliente actualizará tu IP automáticamente
echo.

pause

echo.
echo ========================================
echo RESUMEN DE CONFIGURACIÓN
echo ========================================
echo.

echo [✓] Firewall configurado (puerto 3306)
echo [✓] MySQL localizado
echo [!] Pendiente: Modificar my.ini (bind-address)
echo [!] Pendiente: Reiniciar MySQL
echo [!] Pendiente: Configurar Port Forwarding en router
echo [!] Pendiente: Ejecutar db\setup_central_server.sql
echo.

echo.
echo ========================================
echo SIGUIENTE PASO
echo ========================================
echo.

echo 1. Reinicia MySQL desde Laragon
echo 2. Abre HeidiSQL o phpMyAdmin
echo 3. Ejecuta el archivo: db\setup_central_server.sql
echo 4. Anota la contraseña que configures
echo.

echo ========================================
echo INFORMACIÓN PARA LOS CLIENTES
echo ========================================
echo.

echo Los PCs clientes necesitarán conectarse a:
echo.
echo   IP Local (LAN): %LOCAL_IP%
echo   IP Pública: (ver en whatismyip.com)
echo   DynDNS: (tu dominio.ddns.net)
echo   Puerto: 3306
echo   Usuario: egresapp_remote
echo   Contraseña: (la que configures en el SQL)
echo.

pause
