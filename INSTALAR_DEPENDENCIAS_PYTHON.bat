@echo off
chcp 65001 >nul
echo.
echo ╔════════════════════════════════════════════════════╗
echo ║   EGRESAPP2 - Instalador de Dependencias Python   ║
echo ╔════════════════════════════════════════════════════╗
echo.
echo IMPORTANTE: Este script debe ejecutarse como Administrador
echo.
pause

PowerShell -NoProfile -ExecutionPolicy Bypass -File "%~dp0InstalarDependenciasPython.ps1"

echo.
echo Proceso finalizado.
pause
