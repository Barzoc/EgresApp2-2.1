@echo off
echo ============================================
echo   VERIFICAR E IMPORTAR BASE DE DATOS
echo ============================================
echo.

REM Buscar MySQL
set MYSQL_PATH=
for /d %%i in (C:\laragon\bin\mysql\*) do (
    if exist "%%i\bin\mysql.exe" (
        set MYSQL_PATH=%%i\bin\mysql.exe
        goto :found
    )
)

:found
if "%MYSQL_PATH%"=="" (
    echo [ERROR] No se encontro MySQL
    pause
    exit /b 1
)

echo [OK] MySQL encontrado: %MYSQL_PATH%
echo.

echo Verificando tablas existentes...
"%MYSQL_PATH%" -u root -D gestion_egresados -e "SHOW TABLES;"
echo.

echo ============================================
echo Eliminando y recreando base de datos...
"%MYSQL_PATH%" -u root -e "DROP DATABASE IF EXISTS gestion_egresados;"
"%MYSQL_PATH%" -u root -e "CREATE DATABASE gestion_egresados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo [OK] Base de datos recreada
echo.

echo Importando archivo SQL...
echo Esto puede tardar varios minutos...
echo.

set SQL_FILE=C:\laragon\www\EGRESAPP2\db\gestion_egresados.sql
if not exist "%SQL_FILE%" (
    echo [ERROR] No se encontro el archivo SQL en:
    echo %SQL_FILE%
    pause
    exit /b 1
)

"%MYSQL_PATH%" -u root gestion_egresados < "%SQL_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Base de datos importada correctamente
    echo.
    echo Verificando tablas importadas...
    "%MYSQL_PATH%" -u root -D gestion_egresados -e "SHOW TABLES;"
    echo.
    echo Verificando usuario admin...
    "%MYSQL_PATH%" -u root -D gestion_egresados -e "SELECT email, rol FROM usuarios WHERE rol='admin' LIMIT 1;"
) else (
    echo.
    echo [ERROR] Error al importar la base de datos
)

echo.
pause
