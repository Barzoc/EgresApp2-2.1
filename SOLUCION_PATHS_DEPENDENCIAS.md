# 🔧 Solución: Corrección de PATHs y Dependencias

## ✅ Problema Identificado

El sistema **EGRESAPP2** no podía extraer datos de los expedientes porque:

1. ❌ Las rutas en `config/pdf.php` apuntaban al usuario anterior (`barzo`)
2. ❌ Python no tiene las **dependencias Python** instaladas (pdf2image, paddleocr, numpy)
3. ❌ Tesseract no está en el PATH del sistema

---

## ✅ Rutas Corregidas

### Archivo: `config/pdf.php`

| Herramienta | Ruta Anterior (❌ INCORRECTA) | Ruta Nueva (✅ CORRECTA) |
|------------|-------------------------------|-------------------------|
| **Python** | `C:/Users/barzo/AppData/Local/Programs/Python/Python310/python.exe` | `C:/Users/xerox/AppData/Local/Programs/Python/Python310/python.exe` |
| **Poppler (pdftotext)** | `C:/Users/barzo/AppData/Local/Microsoft/WinGet/Packages/.../poppler-25.07.0/Library/bin/pdftotext.exe` | `C:/Program Files/poppler/Library/bin/pdftotext.exe` |
| **Poppler Path** | `C:/Users/barzo/AppData/Local/Microsoft/WinGet/Packages/.../poppler-25.07.0/Library/bin` | `C:/Program Files/poppler/Library/bin` |

**Estado:** ✅ **YA CORREGIDO AUTOMÁTICAMENTE**

---

## 📦 Dependencias Instaladas en el Sistema

### ✅ Herramientas Disponibles

| Herramienta | Estado | Ubicación |
|------------|--------|-----------|
| **Python 3.10** | ✅ Instalado | `C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe` |
| **Tesseract OCR** | ✅ Instalado | `C:\Program Files\Tesseract-OCR\tesseract.exe` |
| **ImageMagick** | ✅ Instalado | `C:\Program Files\ImageMagick-7.1.2-Q16-HDRI\magick.exe` |
| **Poppler (pdftotext)** | ✅ Instalado | `C:\Program Files\poppler\Library\bin\pdftotext.exe` |

---

## ⚠️ Falta Instalar: Dependencias de Python

### Paquetes Python Faltantes

Actualmente Python **solo tiene** `pip` y `setuptools`. Necesitas instalar:

1. **pdf2image** - Para convertir PDFs a imágenes
2. **paddleocr** - Para OCR avanzado
3. **numpy** - Biblioteca numérica requerida por PaddleOCR

### 🚀 Comando de Instalación

Ejecuta el siguiente comando en PowerShell **como Administrador**:

```powershell
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" -m pip install --upgrade pip
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" -m pip install pdf2image paddleocr numpy pillow paddlepaddle
```

**Tiempo estimado:** 5-10 minutos dependiendo de tu conexión a internet.

---

## 🔍 Verificación Rápida

### 1. Verificar Rutas Corregidas

```powershell
# Verificar Python
Test-Path "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe"

# Verificar Poppler
Test-Path "C:\Program Files\poppler\Library\bin\pdftotext.exe"

# Verificar Tesseract
Test-Path "C:\Program Files\Tesseract-OCR\tesseract.exe"

# Verificar ImageMagick
Test-Path "C:\Program Files\ImageMagick-7.1.2-Q16-HDRI\magick.exe"
```

**Resultado esperado:** Todos deberían devolver `True`

### 2. Verificar Dependencias Python (después de instalar)

```powershell
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" -m pip list
```

**Resultado esperado:** Deberías ver los paquetes:
- `pdf2image`
- `paddleocr`
- `paddlepaddle`
- `numpy`
- `pillow`

### 3. Probar el Script OCR Manualmente

```powershell
# Crear un PDF de prueba y ejecutar el script
$testPdf = "C:\laragon\www\EGRESAPP2\assets\expedientes\expedientes_subidos\test.pdf"
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" "C:\laragon\www\EGRESAPP2\scripts\ocr_paddle.py" --pdf $testPdf --poppler "C:\Program Files\poppler\Library\bin"
```

---

## 🎯 Próximos Pasos

1. ✅ **Rutas corregidas** - Ya está hecho
2. ⏳ **Instalar dependencias Python** - Ejecuta los comandos de arriba
3. 🧪 **Probar la extracción de datos** - Sube un expediente PDF desde la interfaz web

---

## 🆘 Solución de Problemas Adicionales

### Error: "Tesseract no encontrado"

Si aparece un error sobre Tesseract, agrega Tesseract al PATH del sistema:

```powershell
# Ejecutar como Administrador
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\Program Files\Tesseract-OCR", "Machine")
```

Luego **reinicia PowerShell** o tu computadora.

### Error: "ModuleNotFoundError: No module named 'cv2'"

Si PaddleOCR requiere OpenCV:

```powershell
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" -m pip install opencv-python
```

### Error de permisos al instalar paquetes Python

Ejecuta PowerShell **como Administrador** o usa la opción `--user`:

```powershell
& "C:\Users\xerox\AppData\Local\Programs\Python\Python310\python.exe" -m pip install --user pdf2image paddleocr numpy pillow paddlepaddle
```

---

## 📝 Resumen

| Item | Estado |
|------|--------|
| Rutas en `config/pdf.php` | ✅ Corregidas |
| Python instalado | ✅ Sí |
| Tesseract instalado | ✅ Sí |
| ImageMagick instalado | ✅ Sí |
| Poppler instalado | ✅ Sí |
| Dependencias Python | ⚠️ **FALTA INSTALAR** |

**Último paso:** Instala las dependencias Python con el comando proporcionado arriba.

---

## 📧 Contacto

Si encuentras algún error después de seguir estos pasos, revisa los logs de PHP en:
- `C:\laragon\www\EGRESAPP2\instalacion_completa_log.txt`
- Logs de PHP de Laragon
