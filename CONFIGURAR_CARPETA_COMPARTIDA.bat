@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   CONFIGURAR CARPETA COMPARTIDA
echo   (Para PC Maestro)
echo ========================================
echo.
echo Este script configurará una carpeta compartida
echo para sincronizar la base de datos con otros PCs.
echo.
echo REQUISITOS:
echo - Ejecutar como Administrador
echo - Estar en el PC maestro (192.168.1.102)
echo.
pause

REM Verificar privilegios de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ❌ ERROR: Debes ejecutar este script como Administrador
    echo.
    echo Click derecho en el script y selecciona:
    echo "Ejecutar como administrador"
    pause
    exit /b 1
)

cd /d "%~dp0"

set CARPETA_LOCAL=C:\EGRESAPP_BD
set NOMBRE_COMPARTIDO=EGRESAPP_BD

echo.
echo [1/4] Creando carpeta local...
if not exist "%CARPETA_LOCAL%" (
    mkdir "%CARPETA_LOCAL%"
    echo ✅ Carpeta creada: %CARPETA_LOCAL%
) else (
    echo ℹ️  Carpeta ya existe: %CARPETA_LOCAL%
)

echo.
echo [2/4] Compartiendo carpeta en red...
net share %NOMBRE_COMPARTIDO% >nul 2>&1
if %errorlevel% equ 0 (
    echo ℹ️  Carpeta ya compartida, eliminando...
    net share %NOMBRE_COMPARTIDO% /delete /y >nul 2>&1
)

net share %NOMBRE_COMPARTIDO%=%CARPETA_LOCAL% /grant:Everyone,READ >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Carpeta compartida en red
) else (
    echo ❌ Error al compartir carpeta
    pause
    exit /b 1
)

echo.
echo [3/4] Configurando permisos...
icacls "%CARPETA_LOCAL%" /grant Everyone:(OI)(CI)RX /T >nul 2>&1
echo ✅ Permisos configurados

echo.
echo [4/4] Obteniendo información de red...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do set IP=%%a
set IP=%IP: =%

echo.
echo ========================================
echo ✅ CONFIGURACIÓN COMPLETADA
echo ========================================
echo.
echo INFORMACIÓN PARA COMPARTIR:
echo ===========================
echo.
echo Carpeta local: %CARPETA_LOCAL%
echo Carpeta de red: \\%COMPUTERNAME%\%NOMBRE_COMPARTIDO%
echo IP del servidor: %IP%
echo Ruta completa: \\%IP%\%NOMBRE_COMPARTIDO%
echo.
echo INSTRUCCIONES PARA CLIENTES:
echo ============================
echo.
echo En los PCs clientes, cuando ejecuten
echo SINCRONIZAR_BD_CLIENTE.bat, ingresen:
echo.
echo   \\%IP%\%NOMBRE_COMPARTIDO%
echo.
echo o:
echo.
echo   \\%COMPUTERNAME%\%NOMBRE_COMPARTIDO%
echo.
echo ========================================
echo.
echo SIGUIENTE PASO:
echo Ejecuta EXPORTAR_BD_MAESTRO.bat para
echo crear la primera exportación.
echo.
pause
