@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   SINCRONIZAR BD - PC CLIENTE
echo ========================================
echo.
echo Este script sincronizará tu base de datos
echo desde el PC maestro.
echo.
pause

cd /d "%~dp0"

echo.
echo ========================================
echo OPCIONES DE SINCRONIZACIÓN
echo ========================================
echo.
echo 1. Usar carpeta compartida de red
echo 2. Usar archivo local (USB/Drive)
echo.
set /p OPCION="Selecciona opción (1 o 2): "

if "%OPCION%"=="1" goto :carpeta_red
if "%OPCION%"=="2" goto :archivo_local

echo ❌ Opción inválida
pause
exit /b 1

:carpeta_red
echo.
set /p CARPETA_RED="Ingresa ruta de carpeta compartida (ej: \\192.168.1.102\EGRESAPP_BD): "

if not exist "%CARPETA_RED%" (
    echo ❌ No se puede acceder a: %CARPETA_RED%
    echo.
    echo Verifica que:
    echo  - El PC maestro esté encendido
    echo  - La carpeta esté compartida
    echo  - Tengas permisos de acceso
    pause
    exit /b 1
)

php scripts\sync_database_client.php --source="%CARPETA_RED%"
goto :end

:archivo_local
echo.
set /p ARCHIVO="Ingresa ruta completa del archivo .sql: "

if not exist "%ARCHIVO%" (
   echo ❌ Archivo no encontrado: %ARCHIVO%
    pause
    exit /b 1
)

php scripts\sync_database_client.php --file="%ARCHIVO%"
goto :end

:end
if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo ✅ SINCRONIZACIÓN COMPLETADA
    echo ========================================
    echo.
    echo Tu base de datos está actualizada.
    echo Ya puedes usar EGRESAPP2.
    echo.
) else (
    echo.
    echo ========================================
    echo ❌ ERROR EN SINCRONIZACIÓN
    echo ========================================
    echo.
    echo Revisa los mensajes de error arriba.
    echo.
)

pause
