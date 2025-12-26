@echo off
cd /d "%~dp0"
echo Iniciando Sincronizacion de Base de Datos (VP Radmin)...
powershell -NoProfile -ExecutionPolicy Bypass -File "SincronizarBD.ps1"
if %errorlevel% neq 0 (
    echo Hubo un error en la sincronizacion.
    pause
) else (
    echo Sincronizacion Finalizada.
    timeout /t 5
)
