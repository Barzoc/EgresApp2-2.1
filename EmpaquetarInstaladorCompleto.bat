@echo off
:: Empaquetador de Instalador - EGRESAPP2
:: Ejecuta el script de PowerShell para crear el paquete ZIP

echo.
echo ============================================================================
echo    EMPAQUETANDO INSTALADOR COMPLETO - EGRESAPP2
echo ============================================================================
echo.
echo Este script creara un archivo ZIP con todo lo necesario para
echo instalar EGRESAPP2 en otro PC.
echo.
pause

cd /d "%~dp0"
powershell.exe -ExecutionPolicy Bypass -File "%~dp0EmpaquetarInstaladorCompleto.ps1"

if %errorLevel% neq 0 (
    echo.
    echo Error al crear el paquete.
    pause
    exit /B 1
)

echo.
echo Paquete creado exitosamente!
echo Busque el archivo ZIP en la carpeta del proyecto.
echo.
pause
