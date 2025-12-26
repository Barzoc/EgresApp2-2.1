@echo off
echo ============================================
echo   VERIFICAR COLUMNAS DE TABLA USUARIO
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

echo Listando columnas de la tabla 'usuario'...
"%MYSQL_PATH%" -u root -D gestion_egresados -e "DESCRIBE usuario;"

echo.
pause
