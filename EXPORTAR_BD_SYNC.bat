@echo off
:: Script para EXPORTAR la base de datos para sincronización
echo ================================================
echo   EXPORTAR BASE DE DATOS - EGRESAPP2
echo ================================================
echo.

set "CARPETA_DESTINO=C:\EGRESAPP2_SYNC_PACKAGE"
if not exist "%CARPETA_DESTINO%" mkdir "%CARPETA_DESTINO%"

:: 1. Buscar mysqldump.exe
echo [1/3] Buscando mysqldump.exe...
set "MYSQLDUMP_PATH="

:: Buscar en rutas comunes de Laragon
for /d %%D in ("C:\laragon\bin\mysql\mysql*") do (
    if exist "%%D\bin\mysqldump.exe" (
        set "MYSQLDUMP_PATH=%%D\bin\mysqldump.exe"
        goto :FOUND
    )
)

:FOUND
if not defined MYSQLDUMP_PATH (
    echo [ERROR] No se encontro mysqldump.exe en C:\laragon\bin\mysql
    echo Por favor verifica tu instalacion de Laragon.
    pause
    exit /b 1
)

echo [INFO] Encontrado en: %MYSQLDUMP_PATH%

:: 2. Exportar Base de Datos
echo.
echo [2/3] Exportando base de datos 'gestion_egresados'...
"%MYSQLDUMP_PATH%" -u root --opt --events --routines --triggers --default-character-set=utf8 gestion_egresados > "%CARPETA_DESTINO%\gestion_egresados_sync.sql"

if errorlevel 1 (
    echo [ERROR] Fallo la exportacion de la base de datos.
    echo Verifica que el servidor MySQL este corriendo en Laragon.
    pause
    exit /b 1
)

:: 3. Verificar Archivo
echo.
echo [3/3] Verificando archivo exportado...
if exist "%CARPETA_DESTINO%\gestion_egresados_sync.sql" (
    for %%I in ("%CARPETA_DESTINO%\gestion_egresados_sync.sql") do echo [OK] Archivo creado. Tamano: %%~zI bytes
) else (
    echo [ERROR] El archivo SQL no se creo.
)

echo.
echo Exportacion completada!
