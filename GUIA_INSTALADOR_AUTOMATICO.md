# 🚀 GUÍA DEL INSTALADOR AUTOMÁTICO - EGRESAPP2

Esta guía explica cómo usar el instalador automático de dependencias para EGRESAPP2.

---

## 📋 ¿Qué instala automáticamente?

El instalador configura todo lo necesario para ejecutar EGRESAPP2:

### ✅ Software Base
- **Chocolatey** - Gestor de paquetes para Windows
- **Composer** - Gestor de dependencias PHP
- **Tesseract OCR** - Extracción de texto de PDFs (con español)
- **ImageMagick** - Procesamiento y conversión de imágenes
- **LibreOffice** - Conversión de documentos Word a PDF

### ✅ Dependencias PHP (via Composer)
- Google API Client
- Google Cloud Vision
- PDF Parser
- TCPDF y FPDI
- PHPMailer
- PHPWord
- Y todas las dependencias listadas en `composer.json`

### ✅ Librerías JavaScript
- html5-qrcode
- jsQR
- jsPDF

### ✅ Configuración PHP
- Habilita extensiones necesarias en `php.ini`:
  - zip (crítico para PHPWord)
  - gd, curl, mbstring, mysqli, etc.

### ✅ Estructura de Carpetas
- Crea automáticamente las carpetas necesarias:
  - `assets/expedientes/expedientes_subidos/`
  - `certificados/`
  - `temp/`
  - `tmp/`
  - `templates/`

---

## 🎯 CÓMO USAR EL INSTALADOR

### Opción 1: Ejecutar el archivo .BAT (Recomendado)

1. **Hacer clic derecho** en `InstalarDependencias.bat`
2. Seleccionar **"Ejecutar como administrador"**
3. Confirmar el UAC (Control de Cuentas de Usuario)
4. Seguir las instrucciones en pantalla
5. Esperar a que termine (puede tomar 10-20 minutos)

### Opción 2: Ejecutar el script de PowerShell directamente

1. Abrir **PowerShell como Administrador**
2. Navegar a la carpeta del proyecto:
   ```powershell
   cd C:\laragon\www\EGRESAPP2
   ```
3. Ejecutar el script:
   ```powershell
   .\InstalarDependencias.ps1
   ```

---

## ⚠️ REQUISITOS PREVIOS

### Antes de ejecutar el instalador:

1. **Laragon o XAMPP debe estar instalado**
   - El instalador NO instala Laragon/XAMPP
   - Debe tener PHP 8.0+ configurado

2. **Conexión a Internet activa**
   - Se descargarán varios programas (aprox. 500 MB)

3. **Privilegios de Administrador**
   - El instalador DEBE ejecutarse como administrador

4. **Espacio en disco**
   - Mínimo 2 GB libres

---

## 📊 PROCESO DE INSTALACIÓN

El instalador ejecuta los siguientes pasos:

```
1. ✓ Verificación inicial (privilegios de admin)
2. ✓ Instalar Chocolatey
3. ✓ Instalar Composer
4. ✓ Instalar Tesseract OCR (con español)
5. ✓ Instalar ImageMagick
6. ✓ Instalar LibreOffice
7. ✓ Instalar dependencias PHP (composer install)
8. ✓ Descargar librerías JavaScript
9. ✓ Configurar extensiones PHP (php.ini)
10. ✓ Crear carpetas necesarias
11. ✓ Verificación final
```

**Tiempo estimado**: 10-20 minutos (dependiendo de la velocidad de internet)

---

## 📝 ARCHIVO DE LOG

El instalador genera un archivo de log detallado:

**Ubicación**: `instalacion_log.txt` (en la misma carpeta del instalador)

**Contenido**:
- Timestamp de cada operación
- Éxitos y errores
- Detalles de instalación

**Ejemplo**:
```
[2025-11-28 11:45:23] [INFO] Ejecutándose como Administrador
[2025-11-28 11:45:24] [SUCCESS] Chocolatey ya está instalado
[2025-11-28 11:45:30] [SUCCESS] Composer instalado correctamente
[2025-11-28 11:46:15] [SUCCESS] Tesseract OCR instalado correctamente
```

---

## ✅ VERIFICACIÓN POST-INSTALACIÓN

Al finalizar, el instalador muestra un resumen:

```
============================================================================
   RESUMEN DE INSTALACIÓN
============================================================================

Instalaciones exitosas: 15
Errores encontrados: 0

============================================================================
   VERIFICACIÓN FINAL
============================================================================

Composer: ✓ Instalado
  Composer version 2.6.5

Tesseract OCR: ✓ Instalado
  tesseract 5.3.3

ImageMagick: ✓ Instalado
  Version: ImageMagick 7.1.1-21
```

---

## 🔧 PRÓXIMOS PASOS DESPUÉS DE LA INSTALACIÓN

### 1. Reiniciar Apache y MySQL
```
- Abrir Laragon
- Detener todos los servicios
- Iniciar Apache y MySQL nuevamente
```

### 2. Importar la Base de Datos
```powershell
# Opción A: Desde PowerShell
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root < "C:\laragon\www\EGRESAPP2\db\gestion_egresados.sql"

# Opción B: Desde phpMyAdmin
# 1. Abrir http://localhost/phpmyadmin
# 2. Crear base de datos "gestion_egresados"
# 3. Importar el archivo db/gestion_egresados.sql
```

### 3. Configurar Conexión a BD
Editar: `modelo/Conexion.php`
```php
private $host = 'localhost';
private $user = 'root';
private $pass = '';  // Cambiar si tiene contraseña
private $dbname = 'gestion_egresados';
```

### 4. Verificar Acceso
Abrir navegador: `http://localhost/EGRESAPP2`

**Credenciales por defecto**:
- Email: `admin@test.com`
- Contraseña: `admin123`

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "No se puede ejecutar scripts en este sistema"
**Solución**:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Error: "Chocolatey no se instaló correctamente"
**Solución manual**:
1. Abrir PowerShell como Administrador
2. Ejecutar:
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

### Error: "composer install" falla
**Solución**:
```powershell
cd C:\laragon\www\EGRESAPP2
composer clear-cache
composer install --no-cache
```

### Error: "No se encontró php.ini"
**Solución manual**:
1. Buscar php.ini en:
   - `C:\laragon\bin\php\php-8.x.x\php.ini`
   - `C:\xampp\php\php.ini`
2. Editar y descomentar (quitar `;`):
   ```ini
   extension=zip
   extension=gd
   extension=mysqli
   extension=pdo_mysql
   ```

### Tesseract no se encuentra después de instalar
**Solución**:
1. Reiniciar PowerShell/CMD
2. Verificar PATH:
   ```cmd
   echo %PATH%
   ```
3. Si no está, agregar manualmente:
   - `C:\Program Files\Tesseract-OCR`

---

## 📞 VERIFICACIÓN MANUAL

Si el instalador automático falla, puede verificar manualmente:

### Verificar Composer
```cmd
composer --version
```

### Verificar Tesseract
```cmd
tesseract --version
```

### Verificar ImageMagick
```cmd
convert --version
```

### Verificar LibreOffice
```cmd
"C:\Program Files\LibreOffice\program\soffice.exe" --version
```

### Verificar extensiones PHP
```cmd
php -m
```

---

## 🎯 CHECKLIST FINAL

Después de ejecutar el instalador, verificar:

- [ ] Chocolatey instalado
- [ ] Composer instalado
- [ ] Tesseract OCR instalado (con español)
- [ ] ImageMagick instalado
- [ ] LibreOffice instalado
- [ ] Dependencias PHP instaladas (carpeta `vendor/` existe)
- [ ] Librerías JS descargadas
- [ ] Extensión `zip` habilitada en php.ini
- [ ] Carpetas creadas (certificados, temp, etc.)
- [ ] Apache y MySQL reiniciados
- [ ] Base de datos importada
- [ ] Login funcional en http://localhost/EGRESAPP2

---

## 📚 RECURSOS ADICIONALES

- **Documentación completa**: `REQUISITOS_INSTALACION.md`
- **Guía de migración**: `README_Migracion.md`
- **Log de instalación**: `instalacion_log.txt`
- **Backup de php.ini**: `php.ini.backup_YYYYMMDD_HHMMSS`

---

## 💡 CONSEJOS

1. **Ejecutar siempre como Administrador** - Es obligatorio
2. **Tener paciencia** - La instalación puede tomar 10-20 minutos
3. **Revisar el log** - Si algo falla, el log tiene detalles
4. **Hacer backup** - El instalador hace backup automático de php.ini
5. **Reiniciar servicios** - Después de instalar, reiniciar Apache/MySQL

---

## ⚡ INSTALACIÓN RÁPIDA (Resumen)

```bash
# 1. Ejecutar como Administrador
InstalarDependencias.bat

# 2. Esperar a que termine (10-20 min)

# 3. Reiniciar Apache/MySQL

# 4. Importar BD
mysql -u root < db/gestion_egresados.sql

# 5. Configurar modelo/Conexion.php

# 6. Acceder a http://localhost/EGRESAPP2
```

---

**Última actualización**: Noviembre 2025  
**Versión**: 1.0  
**Plataforma**: EGRESAPP2
