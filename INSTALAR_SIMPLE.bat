@echo off
:: ============================================================================
:: INSTALADOR SIMPLE Y FUNCIONAL - EGRESAPP2
:: ============================================================================

cls
echo.
echo ============================================================
echo           INSTALADOR EGRESAPP2 - PASO A PASO
echo ============================================================
echo.
echo Este proceso instalara:
echo   1. Laragon (PHP, MySQL, Apache)
echo   2. Dependencias (Composer, Tesseract, ImageMagick, etc)
echo   3. Copia de archivos del proyecto
echo   4. Base de datos
echo   5. Configuracion de la aplicacion
echo.
echo TIEMPO ESTIMADO: 15-30 minutos
echo.
pause

:: Verificar Admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo [ERROR] Este instalador DEBE ejecutarse como Administrador
    echo.
    echo INSTRUCCIONES:
    echo   1. Cierre esta ventana
    echo   2. Haga CLIC DERECHO en este archivo
    echo   3. Seleccione "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] Ejecutandose como Administrador
echo.
echo ============================================================
echo PASO 1/5: Instalando Laragon
echo ============================================================
echo.

:: Verificar si Laragon ya está instalado
if exist "C:\laragon\laragon.exe" (
    echo [INFO] Laragon ya esta instalado
) else (
    echo [INFO] Instalando Laragon...
    PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0InstalarLaragon.ps1"
    if %errorLevel% neq 0 (
        echo [ERROR] Fallo la instalacion de Laragon
        pause
        exit /b 1
    )
)

echo.
echo ============================================================
echo PASO 2/5: Instalando Dependencias
echo ============================================================
echo.

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0InstalarDependencias.ps1"

echo.
echo ============================================================
echo PASO 3/5: Copiando Archivos del Proyecto
echo ============================================================
echo.

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0CopiarArchivos.ps1"

echo.
echo ============================================================
echo PASO 4/5: Importando Base de Datos
echo ============================================================
echo.

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0ImportarBaseDatos.ps1"

echo.
echo ============================================================
echo PASO 5/5: Creando Acceso Directo
echo ============================================================
echo.

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0CrearAccesoDirecto.ps1"

echo.
echo ============================================================
echo           INSTALACION COMPLETADA
echo ============================================================
echo.
echo [OK] EGRESAPP2 esta listo para usar
echo.
echo COMO USAR:
echo   - Acceso directo en el escritorio: "EGRESAPP2"
echo   - O ejecute: IniciarEGRESAPP2_Auto.bat
echo.
echo CREDENCIALES:
echo   Email: admin@test.com
echo   Contrasena: admin123
echo.
pause
