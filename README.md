# EGRESAPP2 - Sistema de Gestión de Egresados

Sistema integral para el control de documentos, gestión de egresados y generación de certificados.

## 📋 Requisitos del Sistema (Prerrequisitos)

Para que el sistema funcione correctamente en un nuevo entorno (Producción), **ES OBLIGATORIO** tener instalado lo siguiente:

### 1. Servidor Web (Entorno)
- **Laragon (Full Edition)**: Recomendado por su facilidad de uso. Incluye Apache, MySQL 8 y PHP 8.
  - *Alternativa:* XAMPP (requiere configuración manual de puertos y extensiones).
- **Ruta de instalación:** Preferiblemente `C:\laragon` o `D:\laragon`.

### 2. Generación de Documentos (CRÍTICO)
El módulo de certificados requiere software externo para convertir Word a PDF.
- **LibreOffice**: Debe estar instalado en la ruta por defecto.
  - Ruta esperada: `C:\Program Files\LibreOffice\program\soffice.exe`
  - *Sin esto, los certificados se descargarán solo como Word (.docx).*

### 3. Procesamiento de Imágenes (OCR)
Para la lectura automática de expedientes PDF escaneados.
- **Tesseract OCR**: Para reconocimiento de texto.
  - Debe estar agregado al `PATH` de Windows.
- **ImageMagick**: Para manipulación de imágenes previas al OCR.
- **Ghostscript**: Intérprete de PDF.

### 4. Dependencias de Sistema
- **Visual C++ Redistributable (x64)**: Necesario para ciertas extensiones de PHP y Apache.

---

## 🚀 Instalación Automática

Este proyecto incluye un **Instalador Universal** que facilita el despliegue.

### Pasos para instalar:
1.  **Copie** toda la carpeta del proyecto al equipo destino.
2.  Busque el archivo **`Setup_Instalar.bat`** (ícono de engranaje/consola).
3.  Haga **Doble Clic**.
4.  Siga las instrucciones en pantalla.
    - El script detectará si tiene Laragon instalado en `C:` o `D:`.
    - Copiará los archivos a la carpeta `www` correcta.
    - Configurará la conexión a la base de datos automáticamente.

## 🔄 Actualización
Si ya tiene el sistema instalado y desea aplicar cambios de una nueva versión:
1.  Copie la carpeta de la nueva versión.
2.  Ejecute **`Setup_Actualizar.bat`**.
3.  Seleccione la carpeta donde está su sistema actual (si no la detecta sola).
    - *Nota:* Este proceso **RESPETA** sus archivos de configuración (`Conexion.php`), carpetas de expedientes (`assets/expedientes`) y certificados generados.

## ☁️ Sincronización (Opcional)
Si este equipo funcionará como nodo cliente conectado a un servidor central:
- Asegúrese de tener conectividad por red (VPN Radmin o red local).
- Use el botón **"Sincronizar"** en el Dashboard para traer datos del servidor central.