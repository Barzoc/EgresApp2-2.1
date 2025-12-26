@echo off
REM ============================================================================
REM INSTALADOR AUTOMÁTICO DE DEPENDENCIAS - EGRESAPP2
REM Ejecuta el script de PowerShell con privilegios de administrador
REM ============================================================================

echo.
echo ============================================================================
echo    INSTALADOR AUTOMATICO DE DEPENDENCIAS - EGRESAPP2
echo ============================================================================
echo.
echo Este instalador configurara automaticamente:
echo   - Chocolatey (gestor de paquetes)
echo   - Composer (gestor de dependencias PHP)
echo   - Tesseract OCR (extraccion de texto)
echo   - ImageMagick (procesamiento de imagenes)
echo   - LibreOffice (conversion de documentos)
echo   - Dependencias PHP (via Composer)
echo   - Librerias JavaScript
echo   - Extensiones PHP necesarias
echo.
echo IMPORTANTE: Se requieren privilegios de Administrador
echo.
pause

REM Verificar si se ejecuta como administrador
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Ejecutando con privilegios de administrador...
    echo.
) else (
    echo ERROR: Este script debe ejecutarse como Administrador
    echo.
    echo Haga clic derecho en este archivo y seleccione "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

REM Ejecutar el script de PowerShell
echo Iniciando instalacion...
echo.

powershell.exe -ExecutionPolicy Bypass -File "%~dp0InstalarDependencias.ps1"

if %errorLevel% == 0 (
    echo.
    echo ============================================================================
    echo    INSTALACION COMPLETADA
    echo ============================================================================
    echo.
) else (
    echo.
    echo ============================================================================
    echo    INSTALACION COMPLETADA CON ERRORES
    echo ============================================================================
    echo.
    echo Revise el archivo instalacion_log.txt para mas detalles
    echo.
)

pause
