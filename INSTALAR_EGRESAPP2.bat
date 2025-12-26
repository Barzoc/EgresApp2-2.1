@echo off
:: ============================================================================
:: INSTALADOR SIMPLE - EGRESAPP2
:: ============================================================================
:: Usa el instalador original que ya funciona en tu sistema
:: ============================================================================

echo.
echo ========================================================
echo    INSTALADOR EGRESAPP2
echo ========================================================
echo.
echo Este instalador configurara automaticamente:
echo   * Laragon (PHP + MySQL + Apache)
echo   * Composer y dependencias
echo   * Tesseract OCR + ImageMagick
echo   * LibreOffice
echo   * Base de datos
echo   * Acceso directo en escritorio
echo.
echo IMPORTANTE: Este proceso puede tardar 15-30 minutos
echo.
pause

:: Verificar que se ejecuta como Administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo ERROR: Este instalador debe ejecutarse como Administrador
    echo.
    echo Como ejecutar como Administrador:
    echo   1. Haga clic derecho en este archivo
    echo   2. Seleccione "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

:: Ejecutar instalador ORIGINAL (el que ya funcionaba)
PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0InstaladorMaestro.ps1"

echo.
echo ========================================================
echo    Instalacion finalizada
echo ========================================================
echo.
pause
