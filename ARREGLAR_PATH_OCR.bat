@echo off
:: Script MEJORADO para arreglar el PATH de Tesseract, ImageMagick y Poppler
:: Ejecutar como ADMINISTRADOR

echo ================================================
echo   ARREGLAR PATH OCR DINAMICO - EGRESAPP2
echo ================================================
echo.

set "NEW_PATH=%PATH%"
set "CAMBIOS=0"

:: 1. Detectar Tesseract
echo [1/3] Buscando Tesseract...
if exist "C:\Program Files\Tesseract-OCR\tesseract.exe" (
    echo    Encontrado en Program Files.
    echo %PATH% | find /i "C:\Program Files\Tesseract-OCR" > nul
    if errorlevel 1 (
        set "NEW_PATH=%NEW_PATH%;C:\Program Files\Tesseract-OCR"
        set "CAMBIOS=1"
        echo    [AGREGAR] Se agregara al PATH.
    ) else (
        echo    [OK] Ya esta en el PATH.
    )
) else (
    echo    [ALERTA] No se encontro Tesseract. Instalar manualmente.
)

:: 2. Detectar ImageMagick (Cualquier version 7)
echo [2/3] Buscando ImageMagick...
set "IM_FOUND="
for /d %%D in ("C:\Program Files\ImageMagick-7*") do set "IM_FOUND=%%D"

if defined IM_FOUND (
    echo    Encontrado en: %IM_FOUND%
    echo %PATH% | find /i "%IM_FOUND%" > nul
    if errorlevel 1 (
        set "NEW_PATH=%NEW_PATH%;%IM_FOUND%"
        set "CAMBIOS=1"
        echo    [AGREGAR] Se agregara al PATH.
    ) else (
        echo    [OK] Ya esta en el PATH.
    )
) else (
    echo    [ALERTA] No se encontro ImageMagick v7. Instalar manualmente.
)

:: 3. Detectar Poppler (Opcional si se usa binario local)
echo [3/3] Buscando Poppler (sistema)...
if exist "C:\Program Files\poppler\Library\bin\pdftotext.exe" (
    echo    Encontrado en C:\Program Files\poppler
    echo %PATH% | find /i "C:\Program Files\poppler\Library\bin" > nul
    if errorlevel 1 (
        set "NEW_PATH=%NEW_PATH%;C:\Program Files\poppler\Library\bin"
        set "CAMBIOS=1"
        echo    [AGREGAR] Se agregara al PATH.
    ) else (
        echo    [OK] Ya esta en el PATH.
    )
) else (
    echo    [INFO] No se encontro Poppler global (usara local si existe).
)

echo.
if "%CAMBIOS%"=="1" (
    echo Aplicando cambios al PATH del sistema...
    setx PATH "%NEW_PATH%" /M
    if errorlevel 0 (
        echo [EXITO] Variables de entorno actualizadas.
        echo REINICIA EL PC para aplicar cambios.
    ) else (
        echo [ERROR] Fallo al escribir PATH. Ejecuta como ADMINISTRADOR.
    )
) else (
    echo [OK] No se requieren cambios en el PATH. Todo esta correcto.
)

echo.
pause
