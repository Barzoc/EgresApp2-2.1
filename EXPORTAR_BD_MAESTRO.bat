@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   EXPORTAR BASE DE DATOS - PC MAESTRO
echo ========================================
echo.
echo Este script exportará la base de datos
echo para sincronizar con otros PCs.
echo.
pause

cd /d "%~dp0"

REM Configurar variables
set DB_NAME=gestion_egresados
set EXPORT_DIR=db_exports
set SHARED_FOLDER=\\192.168.1.102\EGRESAPP_BD
set TIMESTAMP=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set EXPORT_FILE=%DB_NAME%_%TIMESTAMP%.sql

echo [1/4] Creando directorio de exportación...
if not exist "%EXPORT_DIR%" mkdir "%EXPORT_DIR%"
echo ✅ Directorio listo

echo.
echo [2/4] Exportando base de datos...
echo.
php scripts\export_database_master.php

if %errorlevel% neq 0 (
    echo ❌ Error al exportar
    pause
    exit /b 1
)

echo.
echo [3/4] Copiando a carpeta compartida (opcional)...
if exist "%SHARED_FOLDER%" (
    copy "%EXPORT_DIR%\%EXPORT_FILE%" "%SHARED_FOLDER%\" >nul 2>&1
    if %errorlevel% equ 0 (
        echo ✅ Copiado a carpeta compartida
    ) else (
        echo ⚠️  No se pudo copiar a carpeta compartida
        echo    La BD se exportó localmente en: %EXPORT_DIR%
    )
) else (
    echo ℹ️  Carpeta compartida no configurada
    echo    La BD se exportó localmente en: %EXPORT_DIR%
)

echo.
echo [4/4] Limpiando exportaciones antiguas (mantener últimas 5)...
for /f "skip=5 delims=" %%F in ('dir /b /o-d "%EXPORT_DIR%\*.sql" 2^>nul') do (
    del "%EXPORT_DIR%\%%F" >nul 2>&1
)
echo ✅ Limpieza completa

echo.
echo ========================================
echo ✅ EXPORTACIÓN COMPLETADA
echo ========================================
echo.
echo Archivo: %EXPORT_DIR%\%EXPORT_FILE%
echo.
echo SIGUIENTE PASO:
echo En los PCs clientes, ejecuta:
echo    SINCRONIZAR_BD_CLIENTE.bat
echo.
pause
