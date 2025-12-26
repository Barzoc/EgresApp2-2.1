# ⚠️ Problema: No Se Puede Conectar al Servidor MySQL

## 🔍 Diagnóstico

**IP del servidor**: 192.168.1.102  
**Tu IP (cliente)**: 192.168.1.91  
**Puerto**: 3306  
**Error**: No hay servidor MySQL accesible

---

## ✅ Soluciones (Ejecutar en el PC del Servidor - 192.168.1.102)

### Paso 1: Verificar que MySQL está Ejecutándose

En el **PC servidor** (192.168.1.102):

1. Abre **Laragon**
2. Verifica que **MySQL** está iniciado (botón verde)
3. Si está apagado, click en "Start All"

---

### Paso 2: Configurar MySQL para Aceptar Conexiones Remotas

En el **PC servidor**, necesitas ejecutar:

```cmd
CONFIGURAR_SERVIDOR_CENTRAL.bat
```

Este script hará:
- ✅ Configurar firewall de Windows (puerto 3306)
- ✅ Configurar MySQL para escuchar en la red
- ✅ Crear usuario remoto (`egresapp_remote`)

---

### Paso 3: Modificar my.ini (Si el Script No Funciona)

**En el PC servidor**:

1. Ve a: `C:\laragon\bin\mysql\mysql-[VERSION]\my.ini`
2. Busca la línea:
   ```ini
   bind-address = 127.0.0.1
   ```
3. Cámbiala por:
   ```ini
   bind-address = 0.0.0.0
   ```
4. Guarda el archivo
5. En Laragon: Click derecho en MySQL → **Reload**

---

### Paso 4: Configurar Usuario Remoto en MySQL

**En el PC servidor**, abre HeidiSQL o phpMyAdmin y ejecuta:

```sql
-- Crear usuario remoto
CREATE USER 'egresapp_remote'@'%' IDENTIFIED BY 'TuContraseñaSegura123';

-- Dar permisos completos
GRANT ALL PRIVILEGES ON gestion_egresados.* TO 'egresapp_remote'@'%';

-- Aplicar cambios
FLUSH PRIVILEGES;
```

**⚠️ IMPORTANTE**: Cambia `'TuContraseñaSegura123'` por una contraseña real.

---

### Paso 5: Configurar Firewall de Windows

**En el PC servidor**, ejecuta como administrador:

```cmd
netsh advfirewall firewall add rule name="MySQL Server" dir=in action=allow protocol=TCP localport=3306
```

O ejecuta:
```cmd
CONFIGURAR_SERVIDOR_CENTRAL.bat
```

---

## 🧪 Verificar Configuración (En el Servidor)

Después de configurar, verifica en el **PC servidor**:

```cmd
netstat -an | findstr :3306
```

Deberías ver:
```
TCP    0.0.0.0:3306          0.0.0.0:0              LISTENING
```

Si ves `127.0.0.1:3306`, significa que MySQL solo escucha localmente.

---

## 🔄 Entonces, en el PC Cliente (Tu PC - 192.168.1.91)

Una vez configurado el servidor, prueba nuevamente:

```cmd
php test_mysql_host.php 192.168.1.102
```

Deberías ver:
```
✅ ¡CONEXIÓN EXITOSA!
📍 Servidor encontrado en: 192.168.1.102
```

Luego ejecuta:
```cmd
CONFIGURAR_CLIENTE.bat
```

E ingresa:
- **Host**: `192.168.1.102`
- **Contraseña**: La que configuraste en el servidor

---

## 📋 Checklist de Configuración del Servidor

En el **PC 192.168.1.102**, asegúrate de:

- [ ] Laragon está ejecutándose
- [ ] MySQL está iniciado (verde en Laragon)
- [ ] `my.ini` tiene `bind-address = 0.0.0.0`
- [ ] MySQL reiniciado después de cambiar `my.ini`
- [ ] Usuario `egresapp_remote` creado con permisos
- [ ] Firewall de Windows permite puerto 3306
- [ ] `netstat -an | findstr :3306` muestra `0.0.0.0:3306`

---

## ⚠️ Si Sigue Sin Funcionar

### Verificar Conectividad Básica

Desde tu PC (192.168.1.91):

```cmd
ping 192.168.1.102
```

Si el ping **NO funciona**:
- Los PCs no están en la misma red
- Hay un firewall bloqueando TODO el tráfico
- La IP 192.168.1.102 no es correcta

Si el ping **SÍ funciona** pero MySQL no:
- El problema es específico del puerto 3306
- Revisa firewall y configuración de MySQL

---

## 🆘 Necesitas Ayuda?

Desde el **PC servidor** (192.168.1.102), envíame:

1. Resultado de:
   ```cmd
   ipconfig
   ```

2. Resultado de:
   ```cmd
   netstat -an | findstr :3306
   ```

3. Captura de Laragon mostrando MySQL iniciado

---

**Fecha**: 2025-12-18  
**Cliente**: 192.168.1.91  
**Servidor**: 192.168.1.102  
**Red**: 192.168.1.0/24
