@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   CONFIGURAR CLIENTE EGRESAPP2
echo   Conexión a Base de Datos Central
echo ========================================
echo.

set CONFIG_DIR=%~dp0config
set CONFIG_FILE=%CONFIG_DIR%\database.php

REM Verificar que existe el archivo de plantilla
if not exist "%CONFIG_FILE%" (
    echo ❌ ERROR: No se encontró config\database.php
    echo.
    echo Por favor asegúrate de que el archivo existe.
    pause
    exit /b 1
)

echo Este asistente te ayudará a configurar la conexión
echo al servidor central de EGRESAPP2.
echo.

echo ========================================
echo INFORMACIÓN DEL SERVIDOR CENTRAL
echo ========================================
echo.

echo ¿Cuál es el host del servidor central?
echo.
echo Opciones:
echo   1. Dominio DynDNS (ejemplo: mi-egresapp.ddns.net)
echo   2. IP Pública (ejemplo: 200.123.45.67)
echo   3. IP Local si estás en LAN (ejemplo: 192.168.1.100)
echo.

set /p SERVER_HOST="Ingresa host o IP del servidor: "

if "%SERVER_HOST%"=="" (
    echo ❌ ERROR: Debes ingresar un host válido
    pause
    exit /b 1
)

echo.
set /p DB_PASSWORD="Ingresa la contraseña de la base de datos: "

if "%DB_PASSWORD%"=="" (
    echo ❌ ERROR: Debes ingresar una contraseña
    pause
    exit /b 1
)

echo.
echo ========================================
echo CREANDO CONFIGURACIÓN
echo ========================================
echo.

REM Crear respaldo del archivo actual
if exist "%CONFIG_FILE%" (
    copy "%CONFIG_FILE%" "%CONFIG_FILE%.backup" >nul
    echo [✓] Backup creado: database.php.backup
)

REM Crear archivo de configuración con valores personalizados
powershell -Command ^
"(Get-Content '%CONFIG_FILE%') | ForEach-Object { $_ -replace 'CAMBIAR_POR_TU_DOMINIO\.ddns\.net', '%SERVER_HOST%' } | Set-Content '%CONFIG_FILE%.temp';"

powershell -Command ^
"(Get-Content '%CONFIG_FILE%.temp') | ForEach-Object { $_ -replace 'CAMBIAR_CONTRASEÑA', '%DB_PASSWORD%' } | Set-Content '%CONFIG_FILE%';"

del "%CONFIG_FILE%.temp"

echo [✓] Archivo de configuración actualizado
echo.

echo ========================================
echo PROBANDO CONEXIÓN
echo ========================================
echo.

echo Intentando conectar al servidor central...
echo (Esto puede tomar unos segundos)
echo.

php "%~dp0test_database_connection.php"

if %errorLevel% equ 0 (
    echo.
    echo ========================================
    echo ✅ CONFIGURACIÓN EXITOSA
    echo ========================================
    echo.
    echo El cliente está configurado correctamente.
    echo Ya puedes usar EGRESAPP2.
) else (
    echo.
    echo ========================================
    echo ⚠️  ADVERTENCIA
    echo ========================================
    echo.
    echo La conexión al servidor central falló.
    echo.
    echo Posibles causas:
    echo   • El servidor central no está accesible
    echo   • Credenciales incorrectas
    echo   • Firewall o router bloqueando la conexión
    echo   • Servidor MySQL no configurado para acceso remoto
    echo.
    echo El sistema funcionará en MODO LOCAL por ahora.
    echo Los datos no se sincronizarán hasta que se resuelva.
)

echo.
echo ========================================
echo CONFIGURACIÓN GUARDADA
echo ========================================
echo.

echo Archivo: %CONFIG_FILE%
echo Host: %SERVER_HOST%
echo Puerto: 3306
echo Base de datos: gestion_egresados
echo Usuario: egresapp_remote
echo.
echo Si necesitas cambiar la configuración, puedes:
echo   1. Ejecutar este script nuevamente
echo   2. Editar manualmente: config\database.php
echo.

pause
