@echo off
:: ==========================================
:: SUBIR A GITHUB - EGRESAPP2
:: ==========================================
cd /d "%~dp0"

echo.
echo ===================================================
echo  SUBIR PROYECTO A GITHUB
echo ===================================================
echo.
echo Este script conectara tu proyecto local con un repositorio remoto en GitHub.
echo.

set /p REMOTE_URL="Pega aqui la URL de tu repositorio (https://github.com/usuario/repo.git): "

if "%REMOTE_URL%"=="" (
    echo Error: Debes ingresar una URL.
    pause
    exit /b
)

echo.
echo Configurando remoto 'origin'...
git remote add origin %REMOTE_URL% 2>nul
if %errorlevel% neq 0 (
    echo El remoto ya existe, actualizando URL...
    git remote set-url origin %REMOTE_URL%
)

echo.
echo Configurando optimizaciones de red...
git config http.postBuffer 524288000
git config http.version HTTP/1.1
git config http.lowSpeedLimit 1000
git config http.lowSpeedTime 600

echo Subiendo archivos (branch 'master')...
echo Si es la primera vez, se te pediran credenciales en una ventana emergente.
echo.
git push -u origin master

echo.
if %errorlevel% neq 0 (
    echo Hubo un error al subir. Verifica la URL y tus permisos.
) else (
    echo ¡Subida exitosa!
)
echo.
pause
