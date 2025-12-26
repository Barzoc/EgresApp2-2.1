@echo off
:: ==========================================
:: UPDATE LAUNCHER - EGRESAPP2
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
echo  INICIANDO HERRAMIENTA DE ACTUALIZACION
echo ===================================================
echo.

PowerShell -NoProfile -ExecutionPolicy Bypass -File "HerramientaActualizacion.ps1"

echo.
echo Presione cualquier tecla para cerrar...
pause >nul
