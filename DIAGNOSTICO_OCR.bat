@echo off
:: Script para ejecutar diagnóstico OCR en EGRESAPP2
:: Puede ejecutarse en cualquier PC donde se implemente el sistema

echo ================================================
echo   DIAGNOSTICO OCR - EGRESAPP2
echo ================================================
echo.

:: Verificar si PHP está disponible
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP no esta instalado o no esta en el PATH
    echo Por favor instala Laragon o PHP primero
    pause
    exit /b 1
)

echo Ejecutando diagnostico OCR...
echo.

:: Ejecutar el script PHP y guardar resultado en HTML
php diagnostico_ocr.php > diagnostico_ocr_resultado.html

if %errorlevel% equ 0 (
    echo ================================================
    echo   DIAGNOSTICO COMPLETADO
    echo ================================================
    echo.
    echo El resultado se guardo en: diagnostico_ocr_resultado.html
    echo.
    echo Abriendo resultados en el navegador...
    start diagnostico_ocr_resultado.html
) else (
    echo ERROR: No se pudo ejecutar el diagnostico
    pause
)

echo.
echo Presiona cualquier tecla para salir...
pause >nul
