# 📘 Guía: Sistema de Sincronización de Base de Datos

## 🎯 ¿Qué es?

Sistema simple para compartir la base de datos entre PCs **sin necesidad de configurar MySQL remoto**.

- **PC Maestro** (192.168.1.102) → Exporta BD
- **PCs Clientes** (otros PCs) → Importan BD

---

## 🚀 Configuración Inicial (Una Sola Vez)

### En el PC Maestro (192.168.1.102):

1. **Crear carpeta compartida** (como administrador):
   ```cmd
   CONFIGURAR_CARPETA_COMPARTIDA.bat
   ```

2. **Anotar** la ruta que aparece (ejemplo: `\\192.168.1.102\EGRESAPP_BD`)

---

## 📤 Exportar BD (En el PC Maestro)

Cuando quieras compartir datos con los otros PCs:

```cmd
EXPORTAR_BD_MAESTRO.bat
```

**Hace automáticamente**:
- Exporta toda la BD a archivo SQL
- Guarda en `db_exports/`
- Copia a carpeta compartida (si existe)

**Frecuencia recomendada**:
- Diario (al final del día)
- O cuando hayas hecho cambios importantes

---

## 📥 Sincronizar BD (En PCs Clientes)

Cuando quieras actualizar tu BD:

```cmd
SINCRONIZAR_BD_CLIENTE.bat
```

**Opciones**:

### Opción 1: Carpeta Compartida (Recomendada)
```
Selecciona opción: 1
Ingresa ruta: \\192.168.1.102\EGRESAPP_BD
```

### Opción 2: Archivo Local (USB/Drive)
```
Selecciona opción: 2
Ingresa ruta: D:\backup_bd.sql
```

**El script hará**:
1. Crear backup de tu BD actual
2. Importar la BD nueva
3. Verificar importación

---

## 📊 Flujo de Trabajo Típico

### Escenario: Final del Día

**En PC Maestro (192.168.1.102)**:
```cmd
EXPORTAR_BD_MAESTRO.bat
```

**En Cada PC Cliente**:
```cmd
SINCRONIZAR_BD_CLIENTE.bat
```

---

## 🔧 Solución de Problemas

### ❌ "No se puede acceder a la carpeta compartida"

**En PC Maestro**:
- Verificar que el PC esté encendido
- Ejecutar nuevamente: `CONFIGURAR_CARPETA_COMPARTIDA.bat`
- Verificar firewall de Windows

**En PC Cliente**:
- Hacer ping: `ping 192.168.1.102`
- Intentar abrir en explorador: `\\192.168.1.102\EGRESAPP_BD`

**Alternativa**: Usa USB o Google Drive (Opción 2)

---

### ❌ "Error al importar"

- Verifica que el archivo SQL no esté corrupto
- Revisa el backup creado automáticamente en `db_backups/`
- Intenta la importación nuevamente

---

### ⚠️ "Sobrescribirá tu base de datos local"

**Esto es normal**. El script:
1. ✅ Crea backup antes (en `db_backups/`)
2. ✅ Importa la BD del maestro
3. ✅ Tus datos locales se reemplazan con los del maestro

**Si trabajaste localmente**: Tus cambios se perderán. Asegúrate de:
- Solo el maestro hace cambios, O
- Compartir tus cambios al maestro antes de sincronizar

---

## 📁 Estructura de Archivos

```
EGRESAPP2/
├── EXPORTAR_BD_MAESTRO.bat          (PC Maestro)
├── SINCRONIZAR_BD_CLIENTE.bat       (PC Cliente)
├── CONFIGURAR_CARPETA_COMPARTIDA.bat (PC Maestro - una vez)
├── db_exports/                       (exportaciones)
│   └── gestion_egresados_YYYYMMDD_HHMMSS.sql
└── db_backups/                       (backups automáticos)
    └── backup_before_sync_YYYYMMDD_HHMMSS.sql
```

---

## ✅ Ventajas de Este Sistema

✅ **Simple**: No requiere configurar MySQL remoto  
✅ **Seguro**: Crea backups automáticos  
✅ **Flexible**: Funciona con carpeta compartida, USB o Drive  
✅ **Confiable**: Un solo maestro = sin conflictos de IDs  
✅ **Local**: Cada PC funciona independientemente

---

## ⏰ Programación Automática (Opcional)

Para automatizar la exportación diaria:

**En PC Maestro**, crear tarea programada:
```cmd
schtasks /create /tn "Exportar BD EGRESAPP" /tr "C:\laragon\www\EGRESAPP2\EXPORTAR_BD_MAESTRO.bat" /sc daily /st 18:00
```

---

## 📞 Checklist Rápido

### PC Maestro:
- [ ] `CONFIGURAR_CARPETA_COMPARTIDA.bat` ejecutado
- [ ] Carpeta compartida accesible: `\\192.168.1.102\EGRESAPP_BD`
- [ ] `EXPORTAR_BD_MAESTRO.bat` ejecutado

### Cada PC Cliente:
- [ ] Puede acceder a `\\192.168.1.102\EGRESAPP_BD`
- [ ] `SINCRONIZAR_BD_CLIENTE.bat` ejecutado
- [ ] BD sincronizada exitosamente

---

**Fecha**: 2025-12-18  
**Red**: 192.168.1.0/24  
**Maestro**: 192.168.1.102  
**Cliente**: 192.168.1.91
