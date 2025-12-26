@echo off
REM ============================================================================
REM CREAR PAQUETE DE MIGRACION COMPLETO - EGRESAPP2
REM ============================================================================

echo.
echo ============================================================================
echo    CREAR PAQUETE DE MIGRACION - EGRESAPP2
echo ============================================================================
echo.
echo Este script ejecutara los siguientes pasos:
echo   1. Descargar instaladores offline (Tesseract, ImageMagick, etc.)
echo   2. Exportar la base de datos actualizada
echo   3. Empaquetar todo el proyecto en un archivo ZIP
echo.
echo Presione una tecla para comenzar...
pause >nul

echo.
echo [PASO 1/3] Descargando instaladores...
powershell.exe -ExecutionPolicy Bypass -File "%~dp0DescargarInstaladores.ps1"

echo.
echo [PASO 2/3] Exportando base de datos...
powershell.exe -ExecutionPolicy Bypass -File "%~dp0ExportarBaseDatos.ps1"

echo.
echo [PASO 3/3] Empaquetando proyecto...
powershell.exe -ExecutionPolicy Bypass -File "%~dp0EmpaquetarProyecto.ps1"

echo.
echo ============================================================================
echo    PROCESO FINALIZADO
echo ============================================================================
echo.
pause
