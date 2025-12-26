@echo off
echo ============================================
echo   VERIFICAR ESTADO DE BASE DE DATOS
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

echo [OK] MySQL encontrado
echo.

echo Verificando tablas...
"%MYSQL_PATH%" -u root -D gestion_egresados -e "SHOW TABLES;"
echo.

echo Verificando usuario admin...
"%MYSQL_PATH%" -u root -D gestion_egresados -e "SELECT * FROM usuario WHERE email='admin@test.com';"
echo.

echo.
pause
