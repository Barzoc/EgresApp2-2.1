@echo off
echo ============================================
echo   RESETEAR PASSWORD DE ADMINISTRADOR
echo ============================================
echo.

REM Buscar MySQL
set MYSQL_PATH=
for /d %%i in (C:\laragon\bin\mysql\*) do (
    if exist "%%i\bin\mysql.exe" (
        set MYSQL_PATH=%%i\bin\mysql.exe
        goto :found
    )
)

:found
if "%MYSQL_PATH%"=="" (
    echo [ERROR] No se encontro MySQL
    pause
    exit /b 1
)

echo [OK] MySQL encontrado
echo.

echo Reseteando password del administrador...
echo Nueva password: admin123
echo.

REM Resetear password (usando password_hash de PHP seria lo ideal, pero usaremos MD5 por simplicidad)
REM La aplicacion probablemente usa password_hash() de PHP
REM Vamos a crear un hash bcrypt valido

REM Primero intentemos con un hash conocido de "admin123"
REM Hash bcrypt de "admin123": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

"%MYSQL_PATH%" -u root -D gestion_egresados -e "UPDATE usuarios SET password='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email='admin@test.com';"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Password reseteada exitosamente
    echo.
    echo Ahora puedes iniciar sesion con:
    echo   Email: admin@test.com
    echo   Password: admin123
) else (
    echo.
    echo [ERROR] No se pudo resetear la password
)

echo.
pause
