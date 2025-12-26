# 🚀 GUÍA DE INSTALACIÓN RÁPIDA - EGRESAPP2

## Para Usuarios Finales

### Instalación en 3 Pasos

1. **Hacer clic derecho** en `InstaladorMaestro.bat`
2. Seleccionar **"Ejecutar como administrador"**
3. **Esperar** 15-30 minutos (el instalador hace todo automáticamente)

### Después de la Instalación

1. Buscar el icono **"EGRESAPP2"** en el escritorio
2. **Hacer doble clic** para iniciar la aplicación
3. La aplicación se abrirá automáticamente en el navegador

### Credenciales de Acceso

- **Email:** `admin@test.com`
- **Contraseña:** `admin123`

---

## ¿Qué se Instala Automáticamente?

El instalador configura todo lo necesario:

- ✅ **Laragon** - Servidor web (PHP + MySQL + Apache)
- ✅ **Composer** - Gestor de dependencias PHP
- ✅ **Tesseract OCR** - Extracción de texto de PDFs
- ✅ **ImageMagick** - Procesamiento de imágenes
- ✅ **LibreOffice** - Conversión de documentos
- ✅ **Base de datos** - Importación automática
- ✅ **Acceso directo** - Icono en el escritorio

---

## Requisitos del Sistema

- **Sistema Operativo:** Windows 10/11 (64-bit)
- **RAM:** Mínimo 4 GB (recomendado 8 GB)
- **Espacio en disco:** 5 GB libres
- **Conexión a Internet:** Requerida para la instalación

---

## Solución de Problemas

### La aplicación no abre

1. Verificar que Laragon esté instalado en `C:\laragon`
2. Abrir Laragon manualmente y hacer clic en "Start All"
3. Intentar nuevamente desde el acceso directo del escritorio

### Error durante la instalación

1. Revisar el archivo `instalacion_completa_log.txt`
2. Ejecutar nuevamente el instalador como administrador
3. Verificar conexión a Internet

### No aparece el icono en el escritorio

1. Ejecutar manualmente: `CrearAccesoDirecto.ps1` (clic derecho → Ejecutar como administrador)

---

## Soporte

Para más información, consulte:
- `GUIA_INSTALADOR_AUTOMATICO.md` - Guía técnica completa
- `instalacion_completa_log.txt` - Log de instalación
- `REQUISITOS_INSTALACION.md` - Requisitos detallados

---

**Versión:** 2.0  
**Última actualización:** Diciembre 2024
