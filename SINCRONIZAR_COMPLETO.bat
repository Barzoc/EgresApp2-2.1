@echo off
:: Script MAESTRO para sincronizar PC1 -> PC2
echo ================================================
echo   SISTEMA DE SINCRONIZACION - EGRESAPP2
echo ================================================
echo.
echo Este script preparara un Paquete de Sincronizacion completo.
echo Incluye: Configuracion, Credenciales, Base de Datos y Guias.
echo.
pause

set "CARPETA_DESTINO=C:\EGRESAPP2_SYNC_PACKAGE"

:: 1. Ejecutar copia de configuración
echo.
echo [PASO 1/4] Copiando configuraciones...
call COPIAR_CONFIGURACION.bat
if errorlevel 1 (
    echo [ERROR] Fallo al copiar configuraciones.
    pause
    exit /b 1
)

:: 2. Ejecutar exportación de BD
echo.
echo [PASO 2/4] Exportando base de datos...
call EXPORTAR_BD_SYNC.bat
if errorlevel 1 (
    echo [ERROR] Fallo al exportar la base de datos.
    pause
    exit /b 1
)

:: 3. Copiar script de aplicación al paquete
echo.
echo [PASO 3/4] Preparando instalador para PC2...
copy /Y "APLICAR_SINCRONIZACION.bat" "%CARPETA_DESTINO%\"
copy /Y "GUIA_SINCRONIZACION.md" "%CARPETA_DESTINO%\"

:: 4. Crear Instrucciones rápidas
echo ======================================================== > "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo   INSTRUCCIONES PARA INSTALAR EN EL OTRO PC (PC2) >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo ======================================================== >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo 1. Copia toda esta carpeta ("EGRESAPP2_SYNC_PACKAGE") a una USB o red. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo 2. Pega la carpeta en el Escritorio del PC2. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo 3. Entra a la carpeta y haz doble clic en "APLICAR_SINCRONIZACION.bat". >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo 4. Sigue las instrucciones en pantalla. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"
echo NOTA: Asegurate de que Laragon este instalado en PC2 antes de iniciar. >> "%CARPETA_DESTINO%\LEEME_PRIMERO.txt"

echo.
echo ================================================
echo   PAQUETE CREADO EXITOSAMENTE!
echo ================================================
echo.
echo Carpeta del paquete: %CARPETA_DESTINO%
echo.
echo SIGUIENTES PASOS:
echo 1. Abre la carpeta que se mostrara a continuacion.
echo 2. Copia todo su contenido (o la carpeta misma) al PC2.
echo 3. En PC2, ejecuta 'APLICAR_SINCRONIZACION.bat'.
echo.
pause
explorer "%CARPETA_DESTINO%"
