# 📦 GUÍA DE MIGRACIÓN OFFLINE - EGRESAPP2

Esta guía explica cómo migrar EGRESAPP2 a un equipo **sin conexión a internet** (o con conexión limitada) utilizando el Paquete de Migración Completo.

---

## 🛠️ 1. PREPARACIÓN (EN EL EQUIPO ORIGEN)

Antes de llevar el sistema al nuevo equipo, debes generar el paquete de migración.

1. **Ejecutar `CrearPaqueteMigracion.bat`**
   - Este script hará todo automáticamente:
     - Descargará los instaladores (.exe) de Tesseract, ImageMagick, LibreOffice, etc.
     - Exportará la base de datos actual a `db/gestion_egresados_migracion.sql`.
     - Creará un archivo ZIP con todo el proyecto (ej: `EGRESAPP2_Migracion_20251128_1200.zip`).

2. **Copiar el archivo ZIP**
   - Copia el archivo ZIP generado a un pendrive o disco externo.

---

## 🚀 2. INSTALACIÓN (EN EL EQUIPO DESTINO)

### Requisitos Previos
- Tener instalado **Laragon** o **XAMPP** (Apache + MySQL + PHP 8.0+).
- Copiar el archivo ZIP al nuevo equipo.

### Pasos de Instalación

1. **Descomprimir el proyecto**
   - Extrae el contenido del ZIP en la carpeta `www` (Laragon) o `htdocs` (XAMPP).
   - Ejemplo: `C:\laragon\www\EGRESAPP2`

2. **Ejecutar el Instalador Offline**
   - Abre la carpeta `EGRESAPP2`.
   - Haz clic derecho en **`InstalarDependencias.bat`** y selecciona **"Ejecutar como administrador"**.
   - El script detectará automáticamente los instaladores en la carpeta `installers/` y los instalará sin necesidad de internet.

3. **Restaurar Base de Datos**
   - El instalador te recordará este paso, pero si necesitas hacerlo manualmente:
   - Abre Laragon/XAMPP y asegúrate de que MySQL esté corriendo.
   - Ejecuta en PowerShell (o CMD):
     ```powershell
     # Si usas Laragon
     & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root < "C:\laragon\www\EGRESAPP2\db\gestion_egresados_migracion.sql"
     ```

4. **Configurar Conexión**
   - Verifica `modelo/Conexion.php` para asegurar que las credenciales sean correctas (por defecto usuario `root` sin contraseña).

---

## ✅ 3. VERIFICACIÓN

1. Abre el navegador y ve a: `http://localhost/EGRESAPP2`
2. Inicia sesión con `admin@test.com` / `admin123`.
3. Prueba subir un expediente para verificar que Tesseract (OCR) funciona.
4. Prueba generar un certificado para verificar que LibreOffice funciona.

---

## ❓ PREGUNTAS FRECUENTES

**¿Qué pasa si el equipo destino TIENE internet?**
El instalador funcionará igual de bien. Usará los instaladores locales primero (más rápido) y si falta alguno, intentará descargarlo.

**¿Necesito instalar Composer manualmente?**
No. El instalador incluye `composer-setup.exe` y lo instalará automáticamente. Además, la carpeta `vendor` ya viene incluida en el paquete, por lo que no necesitas ejecutar `composer install` de nuevo.

**¿Qué hago si falla la instalación de Tesseract?**
Ve a la carpeta `installers/` y ejecuta `tesseract-installer.exe` manualmente. Asegúrate de instalar el "Spanish language pack".

---

**Versión del documento**: 1.0 (Offline Edition)
**Fecha**: Noviembre 2025
