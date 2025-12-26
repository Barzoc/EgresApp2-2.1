@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   BUSCAR SERVIDOR MYSQL EN RED
echo ========================================
echo.
echo Tu IP actual: 192.168.1.91
echo Tu red: 192.168.1.0/24
echo.
echo Este script buscará el servidor MySQL en tu red local.
echo Probará las IPs más comunes primero.
echo.
pause

cd /d "%~dp0"

echo.
echo [1/10] Probando 192.168.1.100...
php test_mysql_host.php 192.168.1.100
if %errorlevel% equ 0 goto :found

echo [2/10] Probando 192.168.1.50...
php test_mysql_host.php 192.168.1.50
if %errorlevel% equ 0 goto :found

echo [3/10] Probando 192.168.1.10...
php test_mysql_host.php 192.168.1.10
if %errorlevel% equ 0 goto :found

echo [4/10] Probando 192.168.1.2...
php test_mysql_host.php 192.168.1.2
if %errorlevel% equ 0 goto :found

echo [5/10] Probando 192.168.1.5...
php test_mysql_host.php 192.168.1.5
if %errorlevel% equ 0 goto :found

echo [6/10] Probando 192.168.1.20...
php test_mysql_host.php 192.168.1.20
if %errorlevel% equ 0 goto :found

echo [7/10] Probando 192.168.1.101...
php test_mysql_host.php 192.168.1.101
if %errorlevel% equ 0 goto :found

echo [8/10] Probando 192.168.1.200...
php test_mysql_host.php 192.168.1.200
if %errorlevel% equ 0 goto :found

echo [9/10] Probando 192.168.1.150...
php test_mysql_host.php 192.168.1.150
if %errorlevel% equ 0 goto :found

echo [10/10] Probando 192.168.1.99...
php test_mysql_host.php 192.168.1.99
if %errorlevel% equ 0 goto :found

echo.
echo ========================================
echo ❌ SERVIDOR NO ENCONTRADO
echo ========================================
echo.
echo No se encontró el servidor MySQL en las IPs comunes.
echo.
echo OPCIONES:
echo 1. Verifica que el servidor esté encendido
echo 2. Ejecuta manualmente: php test_mysql_host.php [IP]
echo 3. Pregunta cuál es la IP del servidor central
echo.
goto :end

:found
echo.
echo ========================================
echo ✅ SERVIDOR ENCONTRADO
echo ========================================
echo.
echo Ahora ejecuta:
echo    CONFIGURAR_CLIENTE.bat
echo.
echo Y usa la IP que se mostró arriba.
echo.

:end
pause
