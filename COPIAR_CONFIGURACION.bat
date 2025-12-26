@echo off
:: Script MEJORADO para copiar TODOS los archivos de configuración al otro PC
:: Ejecutar ESTE script en el PC que funciona (PC1)

echo ================================================
echo   COPIAR CONFIGURACION COMPLETA - EGRESAPP2
echo ================================================
echo.

set "CARPETA_DESTINO=C:\EGRESAPP2_SYNC_PACKAGE"

:: Crear carpeta de destino
if not exist "%CARPETA_DESTINO%" (
    mkdir "%CARPETA_DESTINO%"
    mkdir "%CARPETA_DESTINO%\config"
    mkdir "%CARPETA_DESTINO%\lib"
    mkdir "%CARPETA_DESTINO%\controlador"
    mkdir "%CARPETA_DESTINO%\modelo"
) else (
    :: Limpiar carpetas si ya existen para evitar mezclas
    del /Q "%CARPETA_DESTINO%\config\*.*"
    del /Q "%CARPETA_DESTINO%\lib\*.*"
    del /Q "%CARPETA_DESTINO%\controlador\*.*"
    del /Q "%CARPETA_DESTINO%\modelo\*.*"
)

echo [1/5] Copiando archivos de configuracion (config)...
xcopy /Y "config\*.php" "%CARPETA_DESTINO%\config\"
xcopy /Y "config\*.json" "%CARPETA_DESTINO%\config\"

echo [2/5] Copiando librerias y modelos criticos...
xcopy /Y "lib\PDFProcessor.php" "%CARPETA_DESTINO%\lib\"
xcopy /Y "lib\GoogleDriveClient.php" "%CARPETA_DESTINO%\lib\"
xcopy /Y "lib\DriveFolderMapper.php" "%CARPETA_DESTINO%\lib\"
xcopy /Y "modelo\Conexion.php" "%CARPETA_DESTINO%\modelo\"

echo [3/5] Copiando controladores modificados...
xcopy /Y "controlador\ProcesarExpedienteController.php" "%CARPETA_DESTINO%\controlador\"
xcopy /Y "controlador\ExpedienteStorageController.php" "%CARPETA_DESTINO%\controlador\"

echo [4/5] Copiando scripts batch utiles...
xcopy /Y "ARREGLAR_PATH_OCR.bat" "%CARPETA_DESTINO%\"
xcopy /Y "DIAGNOSTICO_OCR.bat" "%CARPETA_DESTINO%\"
xcopy /Y "INSTALAR_DEPENDENCIAS.bat" "%CARPETA_DESTINO%\"

echo.
echo [INFO] Archivos copiados exitosamente a %CARPETA_DESTINO%
echo.
