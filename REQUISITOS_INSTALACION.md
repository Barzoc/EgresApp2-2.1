# 📋 REQUISITOS E INSTALACIÓN - EGRESAPP2

Documentación completa de librerías, dependencias y requisitos necesarios para implementar la plataforma EGRESAPP2 en otro equipo.

---

## 📦 1. SOFTWARE BASE REQUERIDO

### 1.1 Servidor Web Local
**Opción Recomendada: Laragon (Windows)**
- **Descarga**: https://laragon.org/download/
- **Versión**: Laragon Full (64-bit)
- **Incluye**:
  - Apache 2.4+
  - MySQL 8.0+ / MariaDB 10.4+
  - PHP 8.0+

**Alternativa: XAMPP**
- **Descarga**: https://www.apachefriends.org/
- **Versión**: XAMPP 8.0+ (64-bit)

### 1.2 PHP
- **Versión mínima**: PHP 8.0
- **Versión recomendada**: PHP 8.0.30 o superior

#### Extensiones PHP Requeridas:
```ini
extension=curl
extension=fileinfo
extension=gd2
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=zip          # ⚠️ CRÍTICO para PHPWord (.docx)
extension=xml
extension=xmlrpc
extension=intl
```

**Verificar extensiones activas en `php.ini`:**
- Ubicación Laragon: `C:\laragon\bin\php\php-8.x.x\php.ini`
- Ubicación XAMPP: `C:\xampp\php\php.ini`

### 1.3 Base de Datos
- **MySQL**: 8.0+ o **MariaDB**: 10.4+
- **Usuario**: root (o crear usuario específico)
- **Charset**: utf8mb4
- **Collation**: utf8mb4_general_ci

---

## 🔧 2. HERRAMIENTAS EXTERNAS REQUERIDAS

### 2.1 Tesseract OCR (Procesamiento de PDFs)
**Función**: Extracción automática de texto de certificados PDF

**Instalación Windows:**
1. Descargar desde: https://github.com/UB-Mannheim/tesseract/wiki
2. Elegir: `tesseract-ocr-w64-setup-v5.x.x.exe` (64 bits)
3. Durante instalación:
   - ✅ Marcar "Spanish language pack"
   - ✅ Marcar "Add to PATH"
4. Verificar instalación:
```cmd
tesseract --version
```

### 2.2 ImageMagick (Conversión de imágenes)
**Función**: Conversión de PDF a imágenes de alta calidad para OCR

**Instalación Windows:**
1. Descargar desde: https://imagemagick.org/script/download.php#windows
2. Elegir: `ImageMagick-7.x.x-Q16-HDRI-x64-dll.exe`
3. Durante instalación:
   - ✅ Marcar "Add application directory to PATH"
   - ✅ Marcar "Install development headers"
4. Verificar instalación:
```cmd
convert --version
```

### 2.3 LibreOffice (Conversión Word a PDF)
**Función**: Conversión de certificados .docx a .pdf

**Instalación Windows:**
1. Descargar desde: https://www.libreoffice.org/download/download/
2. Instalar versión completa (no portable)
3. Ruta típica: `C:\Program Files\LibreOffice\program\soffice.exe`

---

## 📚 3. DEPENDENCIAS PHP (Composer)

### 3.1 Instalar Composer
**Descarga**: https://getcomposer.org/download/
- Instalar globalmente en Windows
- Verificar: `composer --version`

### 3.2 Dependencias del Proyecto
El archivo `composer.json` incluye:

```json
{
    "require": {
        "google/apiclient": "^2.17",
        "google/cloud-vision": "^2.0",
        "smalot/pdfparser": "^2.12",
        "spatie/pdf-to-text": "^1.54",
        "tecnickcom/tcpdf": "^6.10",
        "phpmailer/phpmailer": "^7.0",
        "setasign/fpdi": "^2.6",
        "phpoffice/phpword": "^1.3"
    }
}
```

**Instalar todas las dependencias:**
```bash
cd C:\laragon\www\EGRESAPP2
composer install
```

#### Descripción de cada librería:

| Librería | Función | Uso en EGRESAPP2 |
|----------|---------|------------------|
| **google/apiclient** | Cliente API de Google | Integración con Google Drive |
| **google/cloud-vision** | Google Vision API | OCR avanzado con IA (opcional) |
| **smalot/pdfparser** | Parser de PDF | Extracción de texto de PDFs |
| **spatie/pdf-to-text** | Wrapper de pdftotext | Conversión PDF a texto |
| **tecnickcom/tcpdf** | Generación de PDFs | Creación de certificados PDF |
| **phpmailer/phpmailer** | Envío de emails | Envío de certificados por correo |
| **setasign/fpdi** | Importador de PDFs | Manipulación de PDFs existentes |
| **phpoffice/phpword** | Manipulación de Word | Generación de certificados .docx |

---

## 🌐 4. LIBRERÍAS JAVASCRIPT (Frontend)

### 4.1 Librerías Incluidas en el Proyecto
Las siguientes librerías ya están en `assets/plugins/`:

```
assets/plugins/
├── bootstrap/          # Framework CSS/JS
├── chart.js/           # Gráficos del dashboard
├── datatables/         # Tablas interactivas
├── datatables-bs4/     # DataTables con Bootstrap 4
├── datatables-buttons/ # Botones de exportación
├── datatables-responsive/ # Tablas responsivas
├── fontawesome-free/   # Iconos
├── jquery/             # jQuery 3.x
├── sweetalert2/        # Alertas modales
├── jszip/              # Compresión para exportar
├── pdfmake/            # Generación PDF frontend
├── html5-qrcode/       # Escáner QR
├── jsqr/               # Lector QR
└── jspdf/              # Generación PDF JS
```

### 4.2 Descargar Librerías Faltantes (si es necesario)
Si alguna librería no está presente, ejecutar en PowerShell:

```powershell
# Desde la raíz del proyecto
cd C:\laragon\www\EGRESAPP2

# html5-qrcode
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/minified/html5-qrcode.min.js" -OutFile ".\assets\plugins\html5-qrcode\html5-qrcode.min.js"

# jsQR
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js" -OutFile ".\assets\plugins\jsqr\jsQR.js"

# jsPDF
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" -OutFile ".\assets\plugins\jspdf\jspdf.umd.min.js"
```

### 4.3 CDN Externo (SweetAlert2)
La aplicación también usa SweetAlert2 desde CDN:
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

---

## 🗄️ 5. BASE DE DATOS

### 5.1 Crear Base de Datos
```sql
CREATE DATABASE gestion_egresados 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;
```

### 5.2 Importar Estructura y Datos
```bash
# Usando MySQL CLI
mysql -u root -p gestion_egresados < C:\laragon\www\EGRESAPP2\db\gestion_egresados.sql

# O usando PowerShell (Laragon)
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root gestion_egresados < "C:\laragon\www\EGRESAPP2\db\gestion_egresados.sql"
```

### 5.3 Tablas Principales
- `egresado` - Datos de egresados
- `titulo` - Catálogo de títulos
- `tituloegresado` - Relación egresado-título
- `usuario` - Usuarios del sistema
- `expediente_queue` - Cola de procesamiento de expedientes

### 5.4 Usuario por Defecto
```
Email: admin@test.com
Contraseña: admin123
```

---

## 🔐 6. CONFIGURACIÓN DE CREDENCIALES

### 6.1 Conexión a Base de Datos
Editar: `modelo/Conexion.php`
```php
private $host = 'localhost';
private $user = 'root';
private $pass = '';  // Cambiar si tiene contraseña
private $dbname = 'gestion_egresados';
```

### 6.2 Google Drive API (Opcional)
Si se usa integración con Google Drive:

1. Crear proyecto en Google Cloud Console
2. Habilitar Google Drive API
3. Descargar credenciales JSON
4. Colocar en: `config/credentials.json`
5. Configurar en: `config/drive.php`

### 6.3 Email (PHPMailer)
Editar: `config/email.php`
```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-contraseña-app';
```

---

## 📁 7. ESTRUCTURA DE CARPETAS REQUERIDAS

### 7.1 Carpetas de Almacenamiento
Crear manualmente si no existen:

```
EGRESAPP2/
├── assets/
│   └── expedientes/
│       └── expedientes_subidos/  # Permisos de escritura
├── certificados/                  # Certificados generados
├── temp/                          # Archivos temporales
├── tmp/                           # Archivos temporales
└── templates/                     # Plantillas Word
    └── MODELO CERTIFICADO TÍTULO.docx
```

### 7.2 Permisos de Escritura
Asegurar que Apache/PHP tenga permisos de escritura en:
- `assets/expedientes/expedientes_subidos/`
- `certificados/`
- `temp/`
- `tmp/`

---

## 🚀 8. PROCESO DE INSTALACIÓN COMPLETO

### Paso 1: Preparar el Entorno
```bash
# 1. Instalar Laragon o XAMPP
# 2. Instalar Tesseract OCR
# 3. Instalar ImageMagick
# 4. Instalar LibreOffice
# 5. Instalar Composer
```

### Paso 2: Copiar Archivos
```bash
# Copiar carpeta EGRESAPP2 a:
C:\laragon\www\EGRESAPP2
# o
C:\xampp\htdocs\EGRESAPP2
```

### Paso 3: Instalar Dependencias PHP
```bash
cd C:\laragon\www\EGRESAPP2
composer install
```

### Paso 4: Configurar PHP
```ini
# Editar php.ini y habilitar:
extension=zip
extension=gd2
extension=mysqli
extension=pdo_mysql
extension=curl
extension=mbstring
extension=xml
```

### Paso 5: Crear Base de Datos
```bash
# Importar SQL
mysql -u root -p < db/gestion_egresados.sql
```

### Paso 6: Configurar Conexión
```php
// Editar modelo/Conexion.php
private $host = 'localhost';
private $user = 'root';
private $pass = '';
```

### Paso 7: Reiniciar Servicios
```bash
# En Laragon: Detener y reiniciar Apache + MySQL
# En XAMPP: Reiniciar desde el panel de control
```

### Paso 8: Verificar Instalación
```
http://localhost/EGRESAPP2
```

---

## ✅ 9. VERIFICACIÓN POST-INSTALACIÓN

### 9.1 Verificar PHP
```bash
php -v
php -m  # Ver extensiones cargadas
```

### 9.2 Verificar Composer
```bash
composer --version
composer show  # Ver paquetes instalados
```

### 9.3 Verificar Herramientas Externas
```bash
tesseract --version
convert --version
soffice --version
```

### 9.4 Verificar Acceso Web
- Login: http://localhost/EGRESAPP2
- Dashboard: http://localhost/EGRESAPP2/vista/adm_egresado.php

### 9.5 Probar Funcionalidades
1. ✅ Login con admin@test.com
2. ✅ Ver tabla de egresados
3. ✅ Subir expediente PDF
4. ✅ Generar certificado
5. ✅ Exportar datos (Excel, PDF)

---

## 🐛 10. SOLUCIÓN DE PROBLEMAS COMUNES

### Error: "Class 'ZipArchive' not found"
**Solución**: Habilitar `extension=zip` en `php.ini`

### Error: Tesseract no encontrado
**Solución**: Verificar que esté en PATH o configurar ruta en código

### Error: No se pueden subir archivos
**Solución**: Verificar permisos de escritura en carpetas

### Error: Composer no instala paquetes
**Solución**: 
```bash
composer clear-cache
composer install --no-cache
```

### Error: MySQL no inicia
**Solución**: Verificar que puerto 3306 no esté ocupado

---

## 📞 11. RECURSOS ADICIONALES

### Documentación Oficial
- PHP: https://www.php.net/docs.php
- Composer: https://getcomposer.org/doc/
- Tesseract: https://github.com/tesseract-ocr/tesseract
- PHPWord: https://phpword.readthedocs.io/
- TCPDF: https://tcpdf.org/

### Archivos de Referencia en el Proyecto
- `INSTALLATION.txt` - Guía de instalación OCR
- `README_Migracion.md` - Guía de migración
- `GUIA_INICIO_AUTOMATICO.md` - Scripts de inicio automático
- `assets/plugins/README_DOWNLOAD_LIBS.md` - Descarga de librerías JS

---

## 📋 12. CHECKLIST DE INSTALACIÓN

- [ ] Laragon/XAMPP instalado
- [ ] PHP 8.0+ configurado
- [ ] Extensión `zip` habilitada en PHP
- [ ] MySQL/MariaDB funcionando
- [ ] Composer instalado
- [ ] Tesseract OCR instalado
- [ ] ImageMagick instalado
- [ ] LibreOffice instalado
- [ ] Proyecto copiado a `www/` o `htdocs/`
- [ ] `composer install` ejecutado
- [ ] Base de datos importada
- [ ] Conexión configurada en `Conexion.php`
- [ ] Carpetas de almacenamiento creadas
- [ ] Permisos de escritura configurados
- [ ] Apache y MySQL reiniciados
- [ ] Login funcional en navegador
- [ ] Tabla de egresados visible
- [ ] Subida de expedientes funcional
- [ ] Generación de certificados funcional

---

## 🎯 RESUMEN EJECUTIVO

**Requisitos Mínimos:**
- Windows 10/11 (64-bit)
- 4 GB RAM
- 2 GB espacio en disco
- Conexión a internet (para instalación inicial)

**Software Esencial:**
1. Laragon/XAMPP (Apache + MySQL + PHP 8.0+)
2. Composer
3. Tesseract OCR
4. ImageMagick
5. LibreOffice

**Tiempo de Instalación Estimado:** 30-45 minutos

**Nivel de Dificultad:** Intermedio

---

**Última actualización**: Noviembre 2025  
**Versión del documento**: 1.0  
**Plataforma**: EGRESAPP2
