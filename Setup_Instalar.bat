@echo off
:: ==========================================
:: INSTALLER LAUNCHER - EGRESAPP2
:: ==========================================
:: Check for Administrator privileges
fltmc >nul 2>&1 || (
    echo.
    echo ---------------------------------------------------
    echo  Solicitando permisos de Administrador...
    echo ---------------------------------------------------
    echo.
    PowerShell Start-Process -FilePath "%0" -Verb RunAs
    exit /b
)

:: Change to script directory
cd /d "%~dp0"

echo.
echo ===================================================
echo  INICIANDO INSTALADOR UNIVERSAL
echo ===================================================
echo.

PowerShell -NoProfile -ExecutionPolicy Bypass -File "InstaladorUniversal.ps1"

echo.
echo Presione cualquier tecla para cerrar...
pause >nul
