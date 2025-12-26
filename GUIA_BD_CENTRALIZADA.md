# 📘 Guía Rápida: Base de Datos Centralizada - EGRESAPP2

> **Objetivo**: Conectar 3-4 PCs al mismo servidor de base de datos por Internet

---

## 🎯 ¿Qué Necesitas?

- ✅ Un PC que será el **SERVIDOR CENTRAL** (donde están los datos actualizados)
- ✅ 3-4 PCs **CLIENTES** que se conectarán al servidor
- ✅ Conexión a Internet en todos los PCs
- ✅ Acceso de administrador en el PC servidor
- ✅ **30 minutos** aproximadamente para la configuración inicial

---

## 📋 PARTE 1: Configurar el PC SERVIDOR (Solo una vez)

### Paso 1: Configurar Windows y Firewall

1. En el PC servidor, **click derecho** en:
   ```
   CONFIGURAR_SERVIDOR_CENTRAL.bat
   ```

2. Selecciona **"Ejecutar como administrador"**

3. El script configurará:
   - ✅ Firewall de Windows (puerto 3306)
   - ✅ Detectará tu IP local
   - 📝 Te mostrará instrucciones para el router

4. **Anota la información** que aparece al final

---

### Paso 2: Configurar MySQL

1. Abre **HeidiSQL** (desde Laragon) o **phpMyAdmin**

2. Abre el archivo:
   ```
   db\setup_central_server.sql
   ```

3. **IMPORTANTE**: Antes de ejecutar, cambia esta línea:
   ```sql
   CREATE USER 'egresapp_remote'@'%' IDENTIFIED BY 'Pass2024!Secure';
   ```
   
   Reemplaza `Pass2024!Secure` por **tu propia contraseña fuerte**
   
   Ejemplo:
   ```sql
   CREATE USER 'egresapp_remote'@'%' IDENTIFIED BY 'MiContraseña123!XYZ';
   ```

4. Ejecuta todo el script (botón▶️ o F9)

5. **Anota la contraseña** que configuraste

---

### Paso 3: Modificar my.ini

1. Abre: `C:\laragon\bin\mysql\mysql-X.X.X\my.ini`
   (Reemplaza X.X.X por tu versión de MySQL)

2. Busca la línea:
   ```ini
   bind-address = 127.0.0.1
   ```

3. Cámbiala por:
   ```ini
   bind-address = 0.0.0.0
   ```

4. **Guarda** el archivo

5. **Reinicia MySQL** desde Laragon:
   - Click derecho en MySQL
   - Selecciona "Reload"

---

### Paso 4: Configurar tu Router (Port Forwarding)

Esta es la parte más importante para acceso por Internet:

1. Abre tu navegador y ve a la configuración del router:
   - Usualmente: `http://192.168.1.1` o `http://192.168.0.1`
   - Usuario/contraseña: (viene en el router o manual)

2. Busca la sección **"Port Forwarding"** o **"Virtual Server"**

3. Crea una nueva regla con estos datos:
   ```
   Nombre/Descripción: MySQL EGRESAPP2
   Puerto Externo: 3306
   Puerto Interno: 3306
   IP Interna: [La IP que anotaste en Paso 1]
   Protocolo: TCP
   ```

4. **Guarda** los cambios

5. **Reinicia** el router (opcional pero recomendado)

---

### Paso 5: Obtener tu IP Pública o Configurar DynDNS

#### Opción A: Usar IP Pública (Más Simple pero puede cambiar)

1. Abre en tu navegador: https://www.whatismyip.com

2. **Anota tu IP pública** (ejemplo: `200.123.45.67`)

3. Esta es la IP que usarán los clientes

⚠️ **Nota**: Tu IP puede cambiar si reinicias el router. Si esto pasa, tendrás que actualizar la configuración en los clientes.

---

#### Opción B: Usar DynDNS (Recomendado - IP no cambia)

1. Crea cuenta gratuita en: https://www.noip.com
   (o https://www.duckdns.org)

2. Crea un **hostname**:
   - Ejemplo: `mi-egresapp.ddns.net`

3. Descarga e instala el **cliente** No-IP:
   - https://www.noip.com/download

4. Configúralo con tus credenciales

5. El cliente actualizará automáticamente tu IP

6. **Anota tu dominio** (ejemplo: `mi-egresapp.ddns.net`)

---

## 📋 PARTE 2: Configurar los PCs CLIENTES (En cada cliente)

### Paso 1: Ejecutar Asistente de Configuración

1. En cada PC cliente, ejecuta:
   ```
   CONFIGURAR_CLIENTE.bat
   ```

2. Te preguntará:
   ```
   Ingresa host o IP del servidor:
   ```
   
   Escribe **UNO** de estos (según lo que configuraste):
   - Si usas DynDNS: `mi-egresapp.ddns.net`
   - Si usas IP pública: `200.123.45.67`
   - Si estás en la misma red (LAN): `192.168.1.100`

3. Te preguntará:
   ```
   Ingresa la contraseña de la base de datos:
   ```
   
   Escribe la contraseña que configuraste en **Parte 1, Paso 2**

4. El script probará la conexión automáticamente

---

### Paso 2: Verificar Conexión

1. Ejecuta:
   ```
   php test_database_connection.php
   ```

2. Deberías ver:
   ```
   ✅ PRUEBA COMPLETADA EXITOSAMENTE
   🌐 Conectado al SERVIDOR CENTRAL
   ```

3. Si aparece `MODO LOCAL ACTIVO`, verifica:
   - ¿El servidor está encendido?
   - ¿Tienes Internet?
   - ¿La IP/dominio es correcto?
   - ¿El firewall del router permite la conexión?

---

## ✅ Verificación Final

### En el Servidor:

Abre HeidiSQL y ejecuta:
```sql
SELECT * FROM information_schema.processlist 
WHERE user = 'egresapp_remote';
```

Deberías ver las conexiones de los clientes.

---

### En Cada Cliente:

1. Abre EGRESAPP2 en el navegador

2. Los datos deben ser **idénticos** en todos los clientes

3. Crea un egresado de prueba en un cliente

4. Actualiza (F5) en otro cliente

5. El nuevo egresado debe aparecer **inmediatamente**

---

## 🔧 Solución de Problemas Comunes

### ❌ "Connection refused" o "Can't connect"

**Causas más comunes:**

1. **Port Forwarding no configurado**
   - Verifica configuración del router
   - Asegúrate que el puerto 3306 está abierto

2. **Firewall bloqueando**
   - Ejecuta `CONFIGURAR_SERVIDOR_CENTRAL.bat` nuevamente
   - Verifica firewall del servidor y router

3. **MySQL no acepta conexiones remotas**
   - Verifica que `my.ini` tenga `bind-address = 0.0.0.0`
   - Reinicia MySQL

4. **IP/Dominio incorrecto**
   - Verifica que la IP pública sea correcta
   - Si usas DynDNS, verifica que esté actualizado

---

### ❌ "Access denied"

**Solución:**

1. Verifica la contraseña en `config\database.php`

2. Ejecuta en el servidor (HeidiSQL):
   ```sql
   SHOW GRANTS FOR 'egresapp_remote'@'%';
   ```

3. Si no existe el usuario, ejecuta nuevamente:
   ```
   db\setup_central_server.sql
   ```

---

### ⚠️ "MODO LOCAL ACTIVO"

**Esto significa:**
- ✅ El sistema funciona PERO
- ⚠️ Estás usando base de datos local
- ❌ NO hay sincronización con el servidor

**Para solucionarlo:**

1. Verifica que el servidor esté encendido

2. Prueba hacer **ping** al servidor:
   ```cmd
   ping mi-egresapp.ddns.net
   ```
   O:
   ```cmd
   ping 200.123.45.67
   ```

3. Si el ping funciona pero no conecta:
   - Verifica puerto 3306 en router
   - Verifica firewall de Windows en servidor

4. Si no hay Internet:
   - El sistema seguirá funcionando en modo local
   - Al volver la conexión, automáticamente reconectará

---

## 📊 Monitoreo

### Ver Conexiones Activas (En el servidor)

```sql
SELECT 
    user,
    host,
    db,
    time,
    state
FROM information_schema.processlist
WHERE user = 'egresapp_remote'
ORDER BY time DESC;
```

---

### Ver Logs de Conexión (En cualquier PC)

Revisar:
```
logs\database.log
```

---

## 🔒 Recomendaciones de Seguridad

1. ✅ Usa contraseña FUERTE (mínimo 12 caracteres)

2. ✅ Configura DynDNS en lugar de IP directa

3. ✅ Considera usar VPN para mayor seguridad

4. ✅ Realiza backups automáticos del servidor central

5. ✅ Cambia la contraseña periódicamente

6. ⚠️ NO compartas la contraseña de la BD

---

## 📞 Resumen de Información Importante

Completa esta tabla con TUS datos:

| Concepto | Tu Valor |
|----------|----------|
| **IP Local del Servidor** | _________________ |
| **IP Pública** | _________________ |
| **Dominio DynDNS** (si aplica) | _________________ |
| **Contraseña BD** | _________________ |
| **Puerto** | 3306 (fijo) |
| **Usuario** | egresapp_remote (fijo) |
| **Base de Datos** | gestion_egresados (fijo) |

**Guarda esta información de forma segura.**

---

## ✅ Checklist Completo

### Servidor:
- [ ] `CONFIGURAR_SERVIDOR_CENTRAL.bat` ejecutado
- [ ] `db\setup_central_server.sql` ejecutado
- [ ] `my.ini` modificado (bind-address = 0.0.0.0)
- [ ] MySQL reiniciado
- [ ] Port Forwarding configurado en router
- [ ] IP Pública/DynDNS obtenida

### Cada Cliente:
- [ ] `CONFIGURAR_CLIENTE.bat` ejecutado
- [ ] Conexión probada exitosamente
- [ ] EGRESAPP2 funcionando
- [ ] Datos sincronizados visibles

---

## 🎉 ¡Listo!

Tu sistema EGRESAPP2 ahora funciona con base de datos centralizada.

**Características activas:**
- ✅ Acceso desde cualquier lugar con Internet
- ✅ Datos siempre sincronizados
- ✅ Respaldo automático a BD local si falla conexión
- ✅ 3-4 PCs trabajando simultáneamente

---

## 📞 Soporte

Si tienes problemas:

1. Revisa `logs\database.log`
2. Ejecuta `php test_database_connection.php`
3. Verifica el checklist anterior
4. Consulta la sección "Solución de Problemas"
