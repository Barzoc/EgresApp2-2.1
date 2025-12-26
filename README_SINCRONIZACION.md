# 🔄 Sistema de Sincronización Automática EGRESAPP2

## 📋 Descripción del Sistema

El sistema implementa **sincronización automática** desde el servidor central hacia todas las bases de datos locales, garantizando que todos los PCs trabajen con datos actualizados.

## 🔀 Flujo de Conexión

Cada vez que se abre la plataforma, el sistema ejecuta automáticamente:

```
┌─────────────────────────────────────────────────────┐
│  1. CONECTAR A BD SERVIDOR_CENTRAL                  │
│     IP: 26.234.93.144 (Radmin VPN)                  │
│     Timeout: 3 segundos                             │
│     Usuario: remoto                                 │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  2. ACTUALIZAR BASE DE DATOS LOCAL (SINCRONIZAR)    │
│     - Copia registros del central (ID < 1,000,000)  │
│     - Sincroniza tabla: egresado                    │
│     - Sincroniza tabla: titulo                      │
│     - Sincroniza tabla: configuracion_certificado   │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  3. CAMBIAR A BASE DE DATOS LOCAL                   │
│     Toda la operación se hace sobre BD local        │
│     Modo: SINCRONIZADO                              │
└─────────────────────────────────────────────────────┘
```

### ⚠️ Modo Fallback

Si el servidor central **NO está disponible**:

```
┌─────────────────────────────────────────────────────┐
│  1. INTENTAR CONECTAR AL SERVIDOR CENTRAL           │
│     ✗ Fallo: Timeout o conexión rechazada           │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  2. TRABAJAR SOLO CON BASE DE DATOS LOCAL           │
│     La aplicación continúa funcionando              │
│     Modo: LOCAL_SOLAMENTE                           │
└─────────────────────────────────────────────────────┘
```

## 📁 Archivos Clave

### 1. `modelo/Conexion.php`
Clase principal que maneja la conexión dual y sincronización automática.

**Métodos importantes:**
- `__construct()` - Ejecuta el flujo completo automáticamente
- `sincronizarDesdeServidor()` - Intenta sincronización
- `copiarDatosCentralALocal()` - Copia datos del central al local
- `conectarLocal()` - Conecta a la BD local para trabajar
- `getModoConexion()` - Retorna el modo actual ('SINCRONIZADO' o 'LOCAL_SOLAMENTE')

### 2. `logs/sincronizacion.log`
Archivo de registro que documenta cada sincronización:
- Fecha y hora de cada intento
- Resultado (exitoso o fallido)
- Cantidad de registros sincronizados
- Errores si los hay

### 3. `test_conexion_dual.php`
Página de prueba visual que muestra:
- Estado de la conexión
- Modo de operación actual
- Datos en la BD local
- Log de sincronización

## 🛠️ Scripts de Configuración

### Para el Servidor Central (PC Principal)

#### `ConfigurarServidorMySQL.ps1`
**Ejecutar UNA SOLA VEZ en el servidor central como Administrador**

Configura:
1. `my.ini` para aceptar conexiones remotas
2. Firewall de Windows (puerto 3306)
3. Usuario remoto en MySQL
4. Reinicia servicio MySQL

```powershell
# En el servidor central:
powershell -ExecutionPolicy Bypass -File "c:\laragon\www\EGRESAPP2\ConfigurarServidorMySQL.ps1"
```

### Para Clientes (PCs que se conectan)

#### `DiagnosticarServidor.ps1`
Verifica conectividad y diagnostica problemas.

```powershell
powershell -ExecutionPolicy Bypass -File "c:\laragon\www\EGRESAPP2\DiagnosticarServidor.ps1"
```

#### `DebugConexion.ps1`
Prueba rápida de conexión MySQL.

```powershell
powershell -ExecutionPolicy Bypass -File "c:\laragon\www\EGRESAPP2\DebugConexion.ps1"
```

## 🔧 Configuración

### Servidor Central

**Requisitos:**
- MySQL configurado para aceptar conexiones remotas
- Puerto 3306 abierto en firewall
- Usuario `remoto` creado con privilegios
- Radmin VPN activo

**IP del servidor:** `26.234.93.144` (configurar en cada cliente)

### Clientes

**Configuración automática:**
La clase `Conexion.php` ya tiene los datos configurados:
- IP Central: 26.234.93.144
- Usuario: remoto
- Contraseña: Sistemas2025!
- Base de datos: gestion_egresados

## 📊 Monitoreo

### Ver logs de sincronización

```powershell
Get-Content c:\laragon\www\EGRESAPP2\logs\sincronizacion.log -Tail 20
```

### Verificar modo actual

```php
<?php
require 'modelo/Conexion.php';
$db = new Conexion();
echo "Modo: " . $db->getModoConexion();
echo "\nÚltima sincronización: " . $db->getUltimaSincronizacion();
?>
```

### Probar sincronización

Visitar: `http://localhost/EGRESAPP2/test_conexion_dual.php`

## 🔐 Seguridad

### Convención de IDs

Para evitar conflictos entre servidor y clientes:

- **Servidor Central:** IDs < 1,000,000
- **Clientes:** IDs >= 1,000,000

Cada PC cliente genera IDs a partir de 1,000,000 en adelante.

### Datos Sincronizados

**Desde Central → Local (Unidireccional):**
- Egresados con ID < 1,000,000
- Todos los títulos
- Configuración de certificados

**Permanecen Solo en Local:**
- Egresados creados localmente (ID >= 1,000,000)

## 🚨 Solución de Problemas

### Error: "No se pudo conectar al central"

**Causa:** Servidor central no disponible o mal configurado

**Solución:**
1. Verificar que Radmin VPN esté conectado
2. Hacer ping a 26.234.93.144
3. Ejecutar `DiagnosticarServidor.ps1`
4. Si el puerto está cerrado, ejecutar `ConfigurarServidorMySQL.ps1` en el servidor

**Impacto:** La aplicación funciona normalmente en modo LOCAL_SOLAMENTE

### Error: "Access denied for user 'remoto'"

**Causa:** Usuario no tiene permisos

**Solución en el servidor central:**
```sql
GRANT ALL PRIVILEGES ON gestion_egresados.* TO 'remoto'@'%';
FLUSH PRIVILEGES;
```

### Sincronización no actualiza datos

**Verificar:**
1. Ver el log: `logs/sincronizacion.log`
2. Verificar que el servidor tenga datos nuevos
3. Confirmar que los IDs sean < 1,000,000

## 📱 Uso Cotidiano

1. **Abrir la plataforma:** La sincronización ocurre automáticamente
2. **Trabajar normalmente:** Todos los cambios se guardan en BD local
3. **Sincronización periódica:** Configurar tarea programada (opcional)

## ✅ Ventajas de Este Sistema

- ✅ **Automático:** No requiere acción del usuario
- ✅ **Rápido:** Timeout de solo 3 segundos
- ✅ **Resiliente:** Si el servidor no responde, trabaja localmente
- ✅ **Transparente:** El usuario no nota la diferencia
- ✅ **Registrado:** Todo queda documentado en logs
- ✅ **Sin duplicados:** Usa REPLACE INTO para evitar conflictos
