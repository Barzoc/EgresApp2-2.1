# 📖 GUÍA DE USO - Scripts de Migración EGRESAPP2

Este documento explica cómo usar todos los scripts implementados del plan de migración.

---

## 🎯 ORDEN DE EJECUCIÓN RECOMENDADO

### Para una NUEVA INSTALACIÓN:

```powershell
# 1. Pre-Flight Checks (Verificar entorno)
.\scripts\PreFlightChecks.ps1

# 2. Instalador Maestro V2 (Instalación completa)
.\InstaladorMaestro_v2.ps1

# 3. Verificación Post-Instalación
.\scripts\VerificacionCompleta.ps1
```

### Para MIGRACIÓN de sistema existente:

```powershell
# 1. Crear Backup Completo
.\scripts\BackupCompleto.ps1

# 2. Pre-Flight Checks
.\scripts\PreFlightChecks.ps1 -ProjectRoot "C:\laragon\www\EGRESAPP2"

# 3. Importar Base de Datos Optimizada
.\scripts\ImportarBaseDatos_v2.ps1 -SQLFile "ruta\al\backup.sql" -OptimizeForSpeed

# 4. Test de Conexión
.\scripts\TestConexionDB.ps1

# 5. Verificación Completa
.\scripts\VerificacionCompleta.ps1
```

---

## 📝 DESCRIPCIÓN DE SCRIPTS

### 🔍 `PreFlightChecks.ps1`
**Propósito**: Verifica que el entorno cumple con todos los requisitos antes de instalar.

**Verifica**:
- Versión y extensiones de PHP (8.0+, mysqli, pdo_mysql, zip, gd, curl, mbstring)
- Disponibilidad de MySQL y conexión
- Permisos de escritura en directorios críticos
- Herramientas externas (Tesseract, ImageMagick, LibreOffice, Composer)
- Puertos de red (80, 3306)

**Uso**:
```powershell
.\scripts\PreFlightChecks.ps1
# O con proyecto en otra ubicación:
.\scripts\PreFlightChecks.ps1 -ProjectRoot "D:\MiProyecto" -MinPHPVersion "8.0.0"
```

**Códigos de salida**:
- `0`: Todos los checks pasaron
- `1`: Algunos checks fallaron (warning)
- `2`: Múltiples checks fallaron (crítico)

---

### 💾 `BackupCompleto.ps1`
**Propósito**: Crea un backup completo del sistema (BD, código, archivos subidos).

**Incluye**:
- Dump de base de datos con checksum SHA256
- Código fuente (excluyendo temporales)
- Archivos subidos por usuarios
- Manifiesto de archivos
- Instrucciones de restauración

**Uso**:
```powershell
.\scripts\BackupCompleto.ps1

# Personalizado:
.\scripts\BackupCompleto.ps1 `
  -ProjectRoot "C:\laragon\www\EGRESAPP2" `
  -BackupRoot "D:\Backups" `
  -DBName "gestion_egresados"
```

**Output**:
- `C:\EGRESAPP2_Backups\[timestamp]\`
  - `gestion_egresados_backup.zip`
  - `EGRESAPP2_codigo.zip`
  - `uploads.zip` (si es <2GB) o carpeta `expedientes_uploads/`
  - `LEEME_BACKUP.txt` (instrucciones)
  - `backup_checksum.txt`

---

### 🚀 `InstaladorMaestro_v2.ps1`
**Propósito**: Instalador mejorado con pre-flight checks y rollback automático.

**Mejoras vs versión original**:
- ✅ Pre-flight checks integrados
- ✅ Sistema de checkpoints
- ✅ Rollback automático en caso de error
- ✅ Backup automático de BD antes de importar
- ✅ Punto de no retorno con confirmación de usuario
- ✅ Mejor manejo de errores

**Uso**:
```powershell
.\InstaladorMaestro_v2.ps1
```

**Fases**:
1. Pre-Flight Checks
2. Instalar Laragon
3. Copiar archivos
4. Instalar dependencias
5. Crear backup (si BD existe)
6. **PUNTO DE NO RETORNO** → Importar BD
7. Configurar conexión
8. Crear acceso directo

---

### 🗄️ `ImportarBaseDatos_v2.ps1`
**Propósito**: Importación optimizada de base de datos con verificación.

**Características**:
- Importación con optimizaciones de velocidad (opcional)
- Estimación de tiempo
- Verificación automática de tablas críticas
- Mejor manejo de errores

**Uso**:
```powershell
# Importación estándar
.\scripts\ImportarBaseDatos_v2.ps1 -SQLFile ".\db\gestion_egresados.sql"

# Importación rápida (para BD grandes)
.\scripts\ImportarBaseDatos_v2.ps1 `
  -SQLFile ".\db\gestion_egresados.sql" `
  -OptimizeForSpeed
```

**Optimizaciones aplicadas** (`-OptimizeForSpeed`):
- Deshabilita `autocommit`
- Deshabilita `unique_checks`
- Deshabilita `foreign_key_checks`
- Se restauran al finalizar

---

### 🧪 `TestConexionDB.ps1`
**Propósito**: Verifica la conexión desde PHP a MySQL.

**Pruebas**:
- Conexión MySQLi
- Conexión PDO
- Listar tablas y contar registros
- Consulta específica a tabla `egresado`

**Uso**:
```powershell
.\scripts\TestConexionDB.ps1

# Proyecto en otra ubicación:
.\scripts\TestConexionDB.ps1 -ProjectRoot "D:\MiProyecto"
```

---

### ✅ Checklists de Verificación

#### `Checklist_1_Servicios.ps1`
Verifica: Apache, MySQL, puertos (80, 3306), PHP

#### `Checklist_2_BaseDatos.ps1`
Verifica: BD existe, tablas críticas, usuario admin, charset

#### `Checklist_3_Archivos.ps1`
Verifica: Archivos core, vendor/, permisos de escritura

#### `Checklist_4_Herramientas.ps1`
Verifica: Tesseract OCR (+ español), ImageMagick, LibreOffice, Composer

#### `Checklist_5_Funcional.ps1`
Verifica: Acceso HTTP, procesamiento PHP, abre navegador para tests manuales

**Uso individual**:
```powershell
.\scripts\Checklist_1_Servicios.ps1
.\scripts\Checklist_2_BaseDatos.ps1
# ... etc
```

---

### 📊 `VerificacionCompleta.ps1`
**Propósito**: Ejecuta TODOS los checklists y genera reporte completo.

**Output**:
- Resultado en consola con colores
- Reporte guardado en `verificacion_[timestamp].txt`
- Abre navegador para verificaciones manuales

**Uso**:
```powershell
.\scripts\VerificacionCompleta.ps1
```

**Interpretación de resultados**:
- ✅ **5/5 checks**: Sistema completamente operativo
- ⚠️ **4/5 checks**: Sistema puede funcionar con limitaciones
- ❌ **≤3/5 checks**: NO usar en producción

---

## 🛠️ SOLUCIÓN DE PROBLEMAS

### Pre-Flight Checks fallan

**Problema**: PHP no encontrado  
**Solución**: Instalar PHP 8.0+ o actualizar rutas en script

**Problema**: MySQL no responde  
**Solución**: Iniciar MySQL desde Laragon

**Problema**: Extensión PHP faltante (ej: `zip`)  
**Solución**: 
```ini
# Editar php.ini
extension=zip
# Reiniciar Apache
```

### Instalador falla

**Si falla ANTES del punto de no retorno**:
- El rollback es automático
- No se hicieron cambios permanentes
- Revisar logs y corregir

**Si falla DESPUÉS de importar BD**:
- El rollback restaura BD desde backup automático
- Revisar `instalacion_completa_log.txt`

### Backup muy lento

**Problema**: Backup de uploads tarda mucho  
**Solución**: 
- Los archivos >2GB NO se comprimen automáticamente
- Considerar backup incremental para uploads grandes

### Importación de BD lenta

**Problema**: Importación tarda >30 minutos  
**Solución**:
```powershell
# Usar modo optimizado
.\scripts\ImportarBaseDatos_v2.ps1 -SQLFile ".\db\backup.sql" -OptimizeForSpeed
```

---

## 📋 CHECKLIST RÁPIDO DE MIGRACIÓN

```
☐ 1. Crear backup del sistema existente
     .\scripts\BackupCompleto.ps1

☐ 2. Verificar entorno
     .\scripts\PreFlightChecks.ps1

☐ 3. Ejecutar instalador
     .\InstaladorMaestro_v2.ps1

☐ 4. Verificar instalación
     .\scripts\VerificacionCompleta.ps1

☐ 5. Pruebas manuales:
     - Login: admin@test.com / admin123
     - Tabla egresados carga
     - Subir PDF de expediente
     - Generar certificado

☐ 6. Documentar cualquier error
```

---

## 📞 AYUDA ADICIONAL

**Logs importantes**:
- `instalacion_completa_log.txt` - Log del instalador
- `verificacion_[timestamp].txt` - Reporte de verificación
- `C:\EGRESAPP2_Backups\[timestamp]\LEEME_BACKUP.txt` - Instrucciones de backup

**Archivos de configuración**:
- `modelo\Conexion.php` - Conexión a BD
- `composer.json` - Dependencias PHP
- `php.ini` - Configuración de PHP (en Laragon)

---

**Versión**: 1.0  
**Última actualización**: Diciembre 2025
