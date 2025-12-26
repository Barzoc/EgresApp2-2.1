@echo off
echo ============================================
echo   COPIAR SCRIPT DE RESET DE PASSWORD
echo ============================================
echo.

if not exist "D:\EGRESAPP2\reset_password.php" (
    echo [ERROR] No se encuentra el archivo reset_password.php en D:\EGRESAPP2
    pause
    exit /b 1
)

echo Copiando archivo...
copy /Y "D:\EGRESAPP2\reset_password.php" "C:\laragon\www\EGRESAPP2\reset_password.php"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Archivo copiado exitosamente.
    echo.
    echo Ahora intenta acceder nuevamente a:
    echo http://localhost/EGRESAPP2/reset_password.php
) else (
    echo.
    echo [ERROR] No se pudo copiar el archivo.
    echo Verifica que tengas permisos de administrador.
)

echo.
pause
