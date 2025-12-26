@echo off
:: ============================================================================
:: LAUNCHER AUTOMÁTICO - EGRESAPP2 (Versión .BAT)
:: ============================================================================
:: Ejecuta el launcher PowerShell de forma simplificada
:: ============================================================================

echo.
echo ========================================================
echo    INICIANDO EGRESAPP2...
echo ========================================================
echo.

:: Ejecutar launcher PowerShell
PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0LauncherAutomatico.ps1"

exit
