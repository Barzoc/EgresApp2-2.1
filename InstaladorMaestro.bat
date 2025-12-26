@echo off
:: ============================================================================
:: INSTALADOR MAESTRO - EGRESAPP2 (Archivo BAT)
:: ============================================================================
:: Este archivo .BAT ejecuta el instalador maestro de PowerShell
:: con privilegios de administrador
:: ============================================================================

echo.
echo ╔══════════════════════════════════════════════════════════════════════════╗
echo ║                                                                          ║
echo ║                  INSTALADOR AUTOMÁTICO - EGRESAPP2                       ║
echo ║                                                                          ║
echo ╚══════════════════════════════════════════════════════════════════════════╝
echo.
echo Solicitando privilegios de administrador...
echo.

:: Verificar privilegios de administrador
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Ejecutando como administrador...
    goto :RunInstaller
) else (
    echo Solicitando elevacion de privilegios...
    goto :UACPrompt
)

:UACPrompt
    echo Set UAC = CreateObject^("Shell.Application"^) > "%temp%\getadmin.vbs"
    echo UAC.ShellExecute "cmd.exe", "/c ""%~f0""", "", "runas", 1 >> "%temp%\getadmin.vbs"
    "%temp%\getadmin.vbs"
    del "%temp%\getadmin.vbs"
    exit /B

:RunInstaller
    cd /d "%~dp0"
    
    :: Ejecutar el instalador maestro de PowerShell
    powershell.exe -ExecutionPolicy Bypass -File "%~dp0InstaladorMaestro.ps1"
    
    if %errorLevel% neq 0 (
        echo.
        echo Error al ejecutar el instalador.
        pause
        exit /B 1
    )
    
    exit /B 0
