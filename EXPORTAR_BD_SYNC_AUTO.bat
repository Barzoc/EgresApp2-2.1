@echo off
:: Script para EXPORTAR la base de datos (VERSION AUTO)
echo ================================================
echo   EXPORTAR BASE DE DATOS - EGRESAPP2
echo ================================================
echo.

set "CARPETA_DESTINO=C:\EGRESAPP2_SYNC_PACKAGE"
if not exist "%CARPETA_DESTINO%" mkdir "%CARPETA_DESTINO%"

:: 1. Buscar mysqldump.exe
set "MYSQLDUMP_PATH="
for /d %%D in ("C:\laragon\bin\mysql\mysql*") do (
    if exist "%%D\bin\mysqldump.exe" (
        set "MYSQLDUMP_PATH=%%D\bin\mysqldump.exe"
        goto :FOUND
    )
)

:FOUND
if not defined MYSQLDUMP_PATH (
    echo [ERROR] No se encontro mysqldump.exe
    exit /b 1
)

echo [INFO] Encontrado en: %MYSQLDUMP_PATH%

:: 2. Exportar Base de Datos
echo.
echo [2/3] Exportando base de datos 'gestion_egresados'...
"%MYSQLDUMP_PATH%" -u root --opt --events --routines --triggers --default-character-set=utf8 gestion_egresados > "%CARPETA_DESTINO%\gestion_egresados_sync.sql"

if errorlevel 1 (
    echo [ERROR] Fallo la exportacion de la base de datos.
    exit /b 1
)

:: 3. Verificar Archivo
if exist "%CARPETA_DESTINO%\gestion_egresados_sync.sql" (
    echo [OK] Archivo creado correctamente.
) else (
    echo [ERROR] El archivo SQL no se creo.
    exit /b 1
)

echo.
echo Exportacion completada!
