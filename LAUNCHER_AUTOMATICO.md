# 🚀 Launcher Automático - EGRESAPP2

Documentación del launcher automático que inicia todos los servicios necesarios para EGRESAPP2.

---

## 📁 Archivos Creados

| Archivo | Descripción | Uso |
|---------|-------------|-----|
| [`LauncherAutomatico.ps1`](file:///d:/EGRESAPP2/LauncherAutomatico.ps1) | Launcher principal (PowerShell) | Ejecutar directamente |
| [`IniciarEGRESAPP2_Auto.bat`](file:///d:/EGRESAPP2/IniciarEGRESAPP2_Auto.bat) | Wrapper .BAT | Doble clic para iniciar |
| [`CrearAccesoDirecto_v2.ps1`](file:///d:/EGRESAPP2/CrearAccesoDirecto_v2.ps1) | Crea acceso directo en escritorio | Ejecutar como admin |

---

## 🎯 ¿Qué hace el Launcher?

El launcher automatiza **5 pasos críticos**:

### ✅ Paso 1: Verifica instalación de Laragon
- Busca Laragon en `C:\laragon\laragon.exe`
- Si no existe, muestra error y sale

### ✅ Paso 2: Inicia servicios de Laragon
- Verifica si Apache (`httpd`) está corriendo
- Verifica si MySQL (`mysqld`) está corriendo
- Si no están corriendo, inicia Laragon automáticamente
- Espera hasta 30 segundos por cada servicio

### ✅ Paso 3: Verifica conexión a base de datos
- Busca el cliente MySQL en ubicaciones comunes
- Intenta conectarse con: `mysql -u root -e "SELECT 1;"`
- Reintenta hasta 10 veces (20 segundos total)
- Si falla, continúa de todos modos pero muestra advertencia

### ✅ Paso 4: Verifica servidor web
- Hace petición HTTP a `http://localhost/`
- Confirma que Apache está respondiendo
- Si falla, continúa (puede estar iniciando)

### ✅ Paso 5: Abre la aplicación
- Abre `http://localhost/EGRESAPP2` en el navegador predeterminado
- Muestra resumen del estado de servicios
- Muestra credenciales por defecto

---

## 🚀 Cómo usar el Launcher

### Opción 1: Archivo .BAT (Más fácil)

```batch
# Hacer doble clic en:
IniciarEGRESAPP2_Auto.bat
```

Este archivo ejecuta el launcher PowerShell automáticamente.

### Opción 2: PowerShell directo

```powershell
cd d:\EGRESAPP2
.\LauncherAutomatico.ps1
```

### Opción 3: Modo silencioso (sin ventana)

```powershell
.\LauncherAutomatico.ps1 -Silent -NoWait
```

### Opción 4: Crear acceso directo en escritorio

```powershell
# Ejecutar como Administrador:
.\CrearAccesoDirecto_v2.ps1
```

Esto creará un icono "🚀 EGRESAPP2" en el escritorio que:
- Inicia todo automáticamente
- Se ejecuta en segundo plano (sin ventana)
- Abre el navegador cuando está listo

---

## 🔧 Parámetros del Launcher

| Parámetro | Descripción | Ejemplo |
|-----------|-------------|---------|
| `-Silent` | No muestra mensajes en consola | `.\LauncherAutomatico.ps1 -Silent` |
| `-NoWait` | No espera tecla al finalizar | `.\LauncherAutomatico.ps1 -NoWait` |

---

## 📊 Flujo del Launcher

```
┌─────────────────────────────────────┐
│ Inicio del Launcher                 │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ ¿Laragon instalado?                 │
│  └─ NO → Error y salir              │
│  └─ SÍ → Continuar                  │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ ¿Apache y MySQL corriendo?          │
│  └─ NO → Iniciar Laragon            │
│  └─ SÍ → Continuar                  │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Esperar Apache (max 30s)            │
│  └─ Verifica proceso 'httpd'        │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Esperar MySQL (max 30s)             │
│  └─ Verifica proceso 'mysqld'       │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Test conexión MySQL (10 reintentos)│
│  └─ mysql -u root -e "SELECT 1;"    │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Verificar HTTP en localhost         │
│  └─ GET http://localhost/           │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Abrir navegador                     │
│  └─ http://localhost/EGRESAPP2      │
└─────────────────┬───────────────────┘
                  │
                  ▼
┌─────────────────────────────────────┐
│ Mostrar resumen y credenciales      │
│  └─ admin@test.com / admin123       │
└─────────────────────────────────────┘
```

---

## 🛠️ Solución de Problemas

### Problema: "Laragon no encontrado"

**Causa**: Laragon no está instalado o está en otra ubicación

**Solución**:
```powershell
# Editar LauncherAutomatico.ps1, línea 18:
$LaragonPath = "C:\laragon\laragon.exe"  # Ajustar ruta
```

### Problema: "Los servicios no se iniciaron"

**Causa**: Laragon no pudo iniciar Apache o MySQL

**Solución**:
1. Abrir Laragon manualmente
2. Hacer clic en "Start All"
3. Verificar logs en `C:\laragon\logs\`

### Problema: "MySQL no responde"

**Causa**: MySQL está iniciando lentamente o no está configurado

**Solución**:
```powershell
# Aumentar tiempo de espera (línea 88):
$maxDbRetries = 20  # Era 10, ahora 20 intentos
```

### Problema: El navegador no se abre

**Causa**: URL incorrecta o navegador predeterminado no configurado

**Solución**:
```powershell
# Editar LauncherAutomatico.ps1, línea 20:
$AppURL = "http://localhost/EGRESAPP2"  # Verificar ruta
```

---

## ⚙️ Configuración Avanzada

### Cambiar puerto de Apache

Si Apache usa un puerto diferente al 80:

```powershell
# Línea 20:
$AppURL = "http://localhost:8080/EGRESAPP2"  # Puerto 8080
```

### Ejecutar al inicio de Windows

1. Presionar `Win + R`
2. Escribir: `shell:startup`
3. Copiar `IniciarEGRESAPP2_Auto.bat` a esta carpeta

Ahora EGRESAPP2 se iniciará automáticamente al encender Windows.

### Agregar notificación de Windows

Agregar después de la línea 231:

```powershell
# Notificación de Windows
Add-Type -AssemblyName System.Windows.Forms
$notifyIcon = New-Object System.Windows.Forms.NotifyIcon
$notifyIcon.Icon = [System.Drawing.SystemIcons]::Information
$notifyIcon.BalloonTipTitle = "EGRESAPP2"
$notifyIcon.BalloonTipText = "Aplicación iniciada correctamente"
$notifyIcon.Visible = $true
$notifyIcon.ShowBalloonTip(3000)
```

---

## 📋 Checklist de Primer Uso

- [ ] Laragon instalado en `C:\laragon\`
- [ ] EGRESAPP2 instalado en `C:\laragon\www\EGRESAPP2\`
- [ ] Base de datos importada (`gestion_egresados`)
- [ ] Ejecutar `CrearAccesoDirecto_v2.ps1` como admin
- [ ] Verificar que aparece icono en escritorio
- [ ] Hacer doble clic en icono
- [ ] Verificar que se abre navegador con login
- [ ] Probar login: `admin@test.com` / `admin123`

---

## 🎨 Características del Launcher

### Visual
- ✅ Mensajes con colores (Verde=éxito, Rojo=error, Amarillo=advertencia)
- ✅ Banner ASCII al inicio
- ✅ Resumen final con estado de servicios
- ✅ Emojis para mejor UX

### Robustez
- ✅ Detecta si servicios ya están corriendo
- ✅ Reintentos automáticos en conexión MySQL (10x)
- ✅ Timeouts configurables
- ✅ Continúa aunque algunos checks fallen

### Flexibilidad
- ✅ Modo silencioso (`-Silent`)
- ✅ Modo no interactivo (`-NoWait`)
- ✅ Ejecutable como .BAT o .PS1
- ✅ Acceso directo en escritorio

---

## 📞 Ayuda Adicional

**Logs importantes**:
- Laragon: `C:\laragon\logs\`
- Apache: `C:\laragon\logs\apache\error.log`
- MySQL: `C:\laragon\data\*.err`

**Comandos útiles**:
```powershell
# Ver procesos de Laragon
Get-Process httpd, mysqld

# Ver puertos en uso
netstat -ano | findstr :80
netstat -ano | findstr :3306

# Reiniciar servicios manualmente
taskkill /f /im httpd.exe
taskkill /f /im mysqld.exe
```

---

**Versión**: 2.0  
**Última actualización**: Diciembre 2025
