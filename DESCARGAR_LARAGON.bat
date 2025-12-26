@echo off
:: ============================================================================
:: DESCARGADOR DE LARAGON - EGRESAPP2
:: ============================================================================

echo.
echo ========================================================
echo    DESCARGANDO LARAGON
echo ========================================================
echo.
echo Este script descargara Laragon (180 MB aprox.)
echo.
pause

:: Verificar Admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo [ERROR] Este script DEBE ejecutarse como Administrador
    echo.
    echo INSTRUCCIONES:
    echo   1. Cierre esta ventana
    echo   2. Haga CLIC DERECHO en este archivo
    echo   3. Seleccione "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0DescargarLaragon.ps1"

exit
