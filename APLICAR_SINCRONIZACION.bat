@echo off
:: Script para APLICAR la sincronización en PC2
:: Ejecutar este script en PC2

echo ================================================
echo   APLICAR SINCRONIZACION - EGRESAPP2 (PC2)
echo ================================================
echo.
echo Este script configurara EGRESAPP2 en este PC con los datos del PC original.
echo.
pause

set "ROOT_DIR=C:\laragon\www\EGRESAPP2"
set "BACKUP_DIR=%~dp0"

:: 1. Validaciones
if not exist "C:\laragon" (
    echo [ERROR] Laragon no esta instalado en C:\laragon
    pause
    exit /b 1
)

if not exist "%ROOT_DIR%" (
    echo [ALERTA] La carpeta del proyecto no existe. Creandola...
    mkdir "%ROOT_DIR%"
    :: Si es una instalacion nueva, deberiamos copiar todo el codigo aqui, 
    :: pero asumimos que el usuario ya copio o clono el repo y esto es solo CONFIG
)

:: 2. Copiar archivos
echo.
echo [1/4] Aplicando archivos de configuracion...

echo Copiando config...
xcopy /Y /S "%BACKUP_DIR%config\*.*" "%ROOT_DIR%\config\"

echo Copiando librerias...
xcopy /Y /S "%BACKUP_DIR%lib\*.*" "%ROOT_DIR%\lib\"

echo Copiando controladores...
xcopy /Y /S "%BACKUP_DIR%controlador\*.*" "%ROOT_DIR%\controlador\"

echo Copiando modelos...
xcopy /Y /S "%BACKUP_DIR%modelo\*.*" "%ROOT_DIR%\modelo\"

echo Copiando scripts raiz...
copy /Y "%BACKUP_DIR%ARREGLAR_PATH_OCR.bat" "%ROOT_DIR%\"
copy /Y "%BACKUP_DIR%DIAGNOSTICO_OCR.bat" "%ROOT_DIR%\"
copy /Y "%BACKUP_DIR%index.php" "%ROOT_DIR%\"
copy /Y "%BACKUP_DIR%validar.php" "%ROOT_DIR%\"
copy /Y "%BACKUP_DIR%check_zip_status.php" "%ROOT_DIR%\"

echo Copiando assets frontend...
mkdir "%ROOT_DIR%\assets\js" 2>nul
copy /Y "%BACKUP_DIR%assets\js\login_rut.js" "%ROOT_DIR%\assets\js\"

:: 3. Importar Base de Datos
echo.
echo [2/4] Importando base de datos...
set "MYSQL_PATH="
for /d %%D in ("C:\laragon\bin\mysql\mysql*") do (
    if exist "%%D\bin\mysql.exe" (
        set "MYSQL_PATH=%%D\bin\mysql.exe"
        goto :FOUND_MYSQL
    )
)
:FOUND_MYSQL

if defined MYSQL_PATH (
    if exist "%BACKUP_DIR%gestion_egresados_sync.sql" (
        echo Importando SQL...
        "%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS gestion_egresados DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;"
        "%MYSQL_PATH%" -u root gestion_egresados < "%BACKUP_DIR%gestion_egresados_sync.sql"
        echo Base de datos importada.
    ) else (
        echo [ERROR] Archivo SQL no encontrado en el paquete.
    )
) else (
    echo [ERROR] No se encontro mysql.exe. Importacion BD saltada.
)

:: 4. Corregir Rutas OCR
echo.
echo [3/4] Ejecutando correccion de rutas OCR...
if exist "%ROOT_DIR%\ARREGLAR_PATH_OCR.bat" (
    cd /d "%ROOT_DIR%"
    call ARREGLAR_PATH_OCR.bat
)

:: 5. Activar Extensión ZIP
echo.
echo [3.5/4] Habilitando extension ZIP en PHP...
if exist "%BACKUP_DIR%ACTIVAR_ZIP.ps1" (
    powershell -ExecutionPolicy Bypass -File "%BACKUP_DIR%ACTIVAR_ZIP.ps1"
)

:: 6. Resumen
echo.
echo [4/4] Verificacion final
if exist "%ROOT_DIR%\config\token.json" echo [OK] Token de Drive instalado.
if exist "%ROOT_DIR%\config\drive.php" echo [OK] Configuracion de Drive instalada.

echo.
echo ================================================
echo   SINCRONIZACION COMPLETADA
echo ================================================
echo.
echo Por favor:
echo 1. Reinicia Laragon (Stop -> Start All)
echo 2. Abre http://localhost/EGRESAPP2
echo.
pause
