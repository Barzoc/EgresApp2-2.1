@echo off
echo ============================================
echo   ACTUALIZANDO ARCHIVOS CORREGIDOS
echo ============================================
echo.

echo Copiando archivos corregidos desde D:\EGRESAPP2 a C:\laragon\www\EGRESAPP2...
echo.

REM Copiar scripts corregidos
copy /Y "D:\EGRESAPP2\InstalarDependencias.ps1" "C:\laragon\www\EGRESAPP2\InstalarDependencias.ps1"
copy /Y "D:\EGRESAPP2\ImportarBaseDatos.ps1" "C:\laragon\www\EGRESAPP2\ImportarBaseDatos.ps1"
copy /Y "D:\EGRESAPP2\InstalarLaragon.ps1" "C:\laragon\www\EGRESAPP2\InstalarLaragon.ps1"
copy /Y "D:\EGRESAPP2\InstaladorMaestro.ps1" "C:\laragon\www\EGRESAPP2\InstaladorMaestro.ps1"

echo.
echo [OK] Archivos actualizados correctamente
echo.
echo Ahora puedes ejecutar el instalador nuevamente desde:
echo C:\laragon\www\EGRESAPP2\InstaladorMaestro.bat
echo.
pause
