# Script de Instalacion Automatica de Dependencias Python para EGRESAPP2
# Ejecutar como Administrador

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " EGRESAPP2 - Instalador de Dependencias" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$pythonPath = "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe"

# Verificar que Python existe
if (!(Test-Path $pythonPath)) {
    Write-Host "ERROR: Python no encontrado en: $pythonPath" -ForegroundColor Red
    Write-Host "Verifica la ruta de instalacion de Python" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "OK: Python encontrado: $pythonPath" -ForegroundColor Green
Write-Host ""

# Actualizar pip
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Paso 1/3: Actualizando pip..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
& $pythonPath -m pip install --upgrade pip
if ($LASTEXITCODE -ne 0) {
    Write-Host "ADVERTENCIA: No se pudo actualizar pip, pero continuaremos..." -ForegroundColor Yellow
} else {
    Write-Host "OK: pip actualizado correctamente" -ForegroundColor Green
}
Write-Host ""

# Instalar dependencias basicas
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Paso 2/3: Instalando dependencias basicas..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Instalando: numpy, pillow, pdf2image..." -ForegroundColor White

& $pythonPath -m pip install numpy pillow pdf2image
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Fallo la instalacion de dependencias basicas" -ForegroundColor Red
    pause
    exit 1
}
Write-Host "OK: Dependencias basicas instaladas" -ForegroundColor Green
Write-Host ""

# Instalar PaddleOCR y PaddlePaddle
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Paso 3/3: Instalando PaddleOCR..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Instalando: paddlepaddle, paddleocr..." -ForegroundColor White
Write-Host "ADVERTENCIA: Esta instalacion puede tardar varios minutos" -ForegroundColor Yellow
Write-Host ""

& $pythonPath -m pip install paddlepaddle paddleocr
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Fallo la instalacion de PaddleOCR" -ForegroundColor Red
    Write-Host "Intentando instalar solo paddleocr (sin paddlepaddle)..." -ForegroundColor Yellow
    & $pythonPath -m pip install paddleocr
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: No se pudo instalar PaddleOCR" -ForegroundColor Red
        pause
        exit 1
    }
}
Write-Host "OK: PaddleOCR instalado correctamente" -ForegroundColor Green
Write-Host ""

# Instalar OpenCV (opcional pero recomendado)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Extra: Instalando OpenCV..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
& $pythonPath -m pip install opencv-python
if ($LASTEXITCODE -ne 0) {
    Write-Host "ADVERTENCIA: No se pudo instalar OpenCV, pero no es critico" -ForegroundColor Yellow
} else {
    Write-Host "OK: OpenCV instalado correctamente" -ForegroundColor Green
}
Write-Host ""

# Verificar instalacion
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Verificando instalacion..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Paquetes instalados:" -ForegroundColor White
& $pythonPath -m pip list

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host " INSTALACION COMPLETADA" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Las dependencias de Python han sido instaladas correctamente." -ForegroundColor White
Write-Host "Ahora puedes probar la extraccion de datos de expedientes." -ForegroundColor White
Write-Host ""

# Test rapido
Write-Host "Deseas ejecutar un test rapido? (S/N)" -ForegroundColor Yellow
$response = Read-Host

if ($response -eq "S" -or $response -eq "s") {
    Write-Host ""
    Write-Host "Ejecutando test de importacion de modulos..." -ForegroundColor Cyan
    
    $testScript = @"
import sys
try:
    import numpy
    print('OK: numpy importado correctamente')
except ImportError as e:
    print(f'ERROR numpy: {e}')
    sys.exit(1)

try:
    import PIL
    print('OK: pillow importado correctamente')
except ImportError as e:
    print(f'ERROR pillow: {e}')
    sys.exit(1)

try:
    import pdf2image
    print('OK: pdf2image importado correctamente')
except ImportError as e:
    print(f'ERROR pdf2image: {e}')
    sys.exit(1)

try:
    import paddleocr
    print('OK: paddleocr importado correctamente')
except ImportError as e:
    print(f'ERROR paddleocr: {e}')
    sys.exit(1)

print('')
print('==============================================')
print(' TODOS LOS MODULOS SE IMPORTARON CORRECTAMENTE')
print('==============================================')
"@
    
    $testScript | & $pythonPath
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "OK: TEST EXITOSO - Todas las dependencias funcionan correctamente" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "ERROR: TEST FALLO - Algunas dependencias tienen problemas" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Presiona cualquier tecla para salir..."
pause
