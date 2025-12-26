@echo off
:: Script para arreglar rutas de OCR directamente en el otro PC
:: Ejecutar ESTE script en el PC que tiene problemas

echo ================================================
echo   ARREGLAR RUTAS OCR - EGRESAPP2
echo ================================================
echo.

cd /d "%~dp0"

echo Este script reemplazara config/pdf.php con una version portable
echo que detecta automaticamente las rutas de Tesseract y Poppler.
echo.
pause

:: Backup del archivo original
if exist "config\pdf.php" (
    copy /Y "config\pdf.php" "config\pdf.php.backup"
    echo [OK] Backup creado: config\pdf.php.backup
)

:: Reemplazar con versión portable
if exist "config\pdf_config_portable.php" (
    copy /Y "config\pdf_config_portable.php" "config\pdf.php"
    echo [OK] Configuracion portable aplicada
) else (
    echo [ERROR] No existe config\pdf_config_portable.php
    echo Por favor copia este archivo primero.
    pause
    exit /b 1
)

echo.
echo ================================================
echo   CONFIGURACION ACTUALIZADA
echo ================================================
echo.
echo La configuracion ahora detecta automaticamente:
echo  - Poppler (pdftotext)
echo  - Python
echo  - Scripts de OCR
echo.
echo SIGUIENTE PASO:
echo 1. Reinicia Laragon
echo 2. Prueba: http://localhost/EGRESAPP2
echo 3. Sube un expediente para probar OCR
echo.
pause
