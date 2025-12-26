@echo off
echo ============================================================================
echo   CORRECCION DE NOMBRES DESDE EXPEDIENTES
echo ============================================================================
echo.
echo Este script extraera los nombres correctos desde los expedientes PDF
echo y actualizara la base de datos con los caracteres especiales correctos.
echo.
echo Presiona cualquier tecla para continuar...
pause > nul

echo.
echo Ejecutando script de correccion...
echo.

php "%~dp0corregir_nombres_desde_expedientes.php"

echo.
echo ============================================================================
echo   PROCESO COMPLETADO
echo ============================================================================
echo.
pause
