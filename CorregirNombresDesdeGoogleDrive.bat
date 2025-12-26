@echo off
echo ============================================================================
echo   CORRECCION DE NOMBRES DESDE GOOGLE DRIVE
echo ============================================================================
echo.
echo Este script descargara los expedientes desde Google Drive,
echo extraera los nombres correctos y actualizara la base de datos.
echo.
echo Presiona cualquier tecla para continuar...
pause > nul

echo.
echo Ejecutando script de correccion...
echo.

php "%~dp0corregir_nombres_desde_drive.php"

echo.
echo ============================================================================
echo   PROCESO COMPLETADO
echo ============================================================================
echo.
pause
