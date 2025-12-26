@echo off
:: Script MEJORADO para copiar TODOS los archivos de configuración (VERSION AUTO)

echo ================================================
echo   COPIAR CONFIGURACION COMPLETA - EGRESAPP2
echo ================================================
echo.

set "CARPETA_DESTINO=C:\EGRESAPP2_SYNC_PACKAGE"

if not exist "%CARPETA_DESTINO%" (
    mkdir "%CARPETA_DESTINO%"
    mkdir "%CARPETA_DESTINO%\config"
    mkdir "%CARPETA_DESTINO%\lib"
    mkdir "%CARPETA_DESTINO%\controlador"
    mkdir "%CARPETA_DESTINO%\modelo"
)

echo [1/5] Copiando archivos de configuracion (config)...
xcopy /Y "config\*.php" "%CARPETA_DESTINO%\config\" >nul
xcopy /Y "config\*.json" "%CARPETA_DESTINO%\config\" >nul

echo [2/5] Copiando librerias y modelos criticos...
xcopy /Y "lib\PDFProcessor.php" "%CARPETA_DESTINO%\lib\" >nul
xcopy /Y "lib\GoogleDriveClient.php" "%CARPETA_DESTINO%\lib\" >nul
xcopy /Y "lib\GoogleDriveClient.php" "%CARPETA_DESTINO%\lib\" >nul
xcopy /Y "lib\DriveFolderMapper.php" "%CARPETA_DESTINO%\lib\" >nul
xcopy /Y "lib\PowerShellTemplateProcessor.php" "%CARPETA_DESTINO%\lib\" >nul
xcopy /Y "modelo\Conexion.php" "%CARPETA_DESTINO%\modelo\" >nul

echo [3/5] Copiando controladores modificados...
xcopy /Y "controlador\ProcesarExpedienteController.php" "%CARPETA_DESTINO%\controlador\" >nul
xcopy /Y "controlador\ExpedienteStorageController.php" "%CARPETA_DESTINO%\controlador\" >nul
xcopy /Y "controlador\GenerarCertificadoWord.php" "%CARPETA_DESTINO%\controlador\" >nul

echo [4/5] Copiando scripts batch utiles...
xcopy /Y "ARREGLAR_PATH_OCR.bat" "%CARPETA_DESTINO%\" >nul
xcopy /Y "DIAGNOSTICO_OCR.bat" "%CARPETA_DESTINO%\" >nul
xcopy /Y "InstalarDependencias.bat" "%CARPETA_DESTINO%\" >nul

echo [INFO] Archivos copiados exitosamente a %CARPETA_DESTINO%

echo [EXTRA] Copiando archivos de validacion y frontend...
xcopy /Y "index.php" "%CARPETA_DESTINO%\" >nul
xcopy /Y "validar.php" "%CARPETA_DESTINO%\" >nul
xcopy /Y "assets\js\login_rut.js" "%CARPETA_DESTINO%\assets\js\" >nul
xcopy /Y "ACTIVAR_ZIP.ps1" "%CARPETA_DESTINO%\" >nul
xcopy /Y "check_zip_status.php" "%CARPETA_DESTINO%\" >nul

echo.
