@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   RENOVAR TOKEN GOOGLE DRIVE
echo ========================================
echo.
echo Este script renovará la autorización de Google Drive.
echo El proceso tomará aproximadamente 2 minutos.
echo.
pause

cd /d "%~dp0"

echo.
echo [1/3] Eliminando token antiguo...
if exist "config\token.json" (
    del "config\token.json"
    echo ✅ Token antiguo eliminado
) else (
    echo ℹ️ No había token anterior
)

echo.
echo [2/3] Iniciando proceso de autorización...
echo.
echo INSTRUCCIONES:
echo 1. Se abrirá un enlace para autorizar en Google
echo 2. Inicia sesión con tu cuenta de Google
echo 3. Autoriza la aplicación EGRESAPP2
echo 4. Serás redirigido a http://localhost (página en blanco)
echo 5. Copia la URL completa que aparece en la barra de direcciones
echo 6. Pégala aquí cuando se te solicite
echo.
echo IMPORTANTE: Copia TODA la URL que empieza con:
echo http://localhost/?code=...
echo.

php scripts\authorize_drive.php

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo ✅ ¡AUTORIZACIÓN EXITOSA!
    echo ========================================
    echo.
    echo El token se ha renovado correctamente.
    echo Ya puedes subir y sincronizar expedientes.
    echo.
) else (
    echo.
    echo ========================================
    echo ❌ ERROR EN LA AUTORIZACIÓN
    echo ========================================
    echo.
    echo Por favor revisa los mensajes de error arriba.
    echo.
)

echo.
pause
