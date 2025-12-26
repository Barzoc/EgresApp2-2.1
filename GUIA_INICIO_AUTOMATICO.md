# 🚀 Guía de Inicio Automático - EGRESAPP2

Esta guía te ayudará a configurar el inicio automático de la plataforma EGRESAPP2.

---

## ✅ Opción 1: Inicio Manual con Un Clic (Recomendado)

### Uso Básico

1. Ve a la carpeta del proyecto: `C:\laragon\www\EGRESAPP2\`
2. Haz **doble clic** en el archivo `IniciarEGRESAPP2.bat`
3. ¡Listo! La plataforma se abrirá automáticamente en tu navegador

### ¿Qué hace el script?

- ✅ Inicia Laragon si no está corriendo
- ✅ Inicia los servicios Apache y MySQL
- ✅ Abre automáticamente la plataforma en tu navegador
- ✅ Muestra el progreso en una ventana

### Crear Acceso Directo en el Escritorio

Para mayor comodidad, puedes crear un acceso directo:

1. Haz **clic derecho** en `IniciarEGRESAPP2.bat`
2. Selecciona **"Enviar a" → "Escritorio (crear acceso directo)"**
3. Ahora puedes iniciar la plataforma desde tu escritorio

**Opcional:** Personalizar el icono del acceso directo:
1. Clic derecho en el acceso directo → **Propiedades**
2. Pestaña **"Acceso directo"** → botón **"Cambiar icono"**
3. Selecciona un icono de tu preferencia

---

## 🔄 Opción 2: Inicio Automático con Windows

Si quieres que la plataforma se inicie automáticamente al encender tu computadora:

### Método A: Carpeta de Inicio (Más Simple)

1. Presiona `Windows + R`
2. Escribe: `shell:startup` y presiona Enter
3. Se abrirá la carpeta de inicio de Windows
4. Copia el archivo `IniciarEGRESAPP2.bat` a esta carpeta
5. ¡Listo! La plataforma se iniciará automáticamente al encender Windows

### Método B: Tarea Programada (Más Control)

1. Presiona `Windows + R`
2. Escribe: `taskschd.msc` y presiona Enter
3. En el panel derecho, clic en **"Crear tarea básica"**
4. Nombre: `Iniciar EGRESAPP2`
5. Desencadenador: **"Al iniciar sesión"**
6. Acción: **"Iniciar un programa"**
7. Programa: `C:\laragon\www\EGRESAPP2\IniciarEGRESAPP2.bat`
8. Finalizar

---

## ⚙️ Configuración Avanzada

### Cambiar la Ruta de Laragon

Si Laragon está instalado en otra ubicación:

1. Abre `IniciarEGRESAPP2.ps1` con un editor de texto
2. Busca la línea: `$laragonPath = "C:\laragon\laragon.exe"`
3. Cambia la ruta a la ubicación correcta
4. Guarda el archivo

### Cambiar el Tiempo de Espera

Si los servicios tardan más en iniciarse:

1. Abre `IniciarEGRESAPP2.ps1`
2. Busca: `Start-Sleep -Seconds 5`
3. Cambia `5` por el número de segundos deseado (ej: `10`)
4. Guarda el archivo

---

## 🔧 Solución de Problemas

### El script no inicia Laragon

**Problema:** Mensaje "No se encontró Laragon"

**Solución:**
- Verifica que Laragon esté instalado en `C:\laragon\`
- Si está en otra ubicación, ajusta la ruta en el script (ver "Configuración Avanzada")

### Los servicios no se inician

**Problema:** Apache o MySQL no se inician automáticamente

**Solución:**
1. Abre Laragon manualmente
2. Verifica que los servicios se puedan iniciar desde Laragon
3. Si hay errores, revisa los logs de Laragon
4. Aumenta el tiempo de espera en el script

### El navegador no se abre

**Problema:** Los servicios inician pero el navegador no abre la plataforma

**Solución:**
- Abre manualmente: `http://localhost/EGRESAPP2/index.php`
- Verifica que Apache esté corriendo
- Revisa que el proyecto esté en `C:\laragon\www\EGRESAPP2\`

### Error de permisos de PowerShell

**Problema:** "No se puede ejecutar scripts en este sistema"

**Solución:**
1. Abre PowerShell como **Administrador**
2. Ejecuta: `Set-ExecutionPolicy RemoteSigned -Scope CurrentUser`
3. Confirma con `Y`
4. Intenta ejecutar el script nuevamente

---

## 📝 Notas Importantes

- ⚠️ El script requiere que Laragon esté instalado
- ⚠️ Los servicios Apache y MySQL deben estar configurados en Laragon
- ⚠️ El proyecto debe estar en `C:\laragon\www\EGRESAPP2\`
- ✅ Compatible con Windows 10 y 11
- ✅ No requiere permisos de administrador (en la mayoría de casos)

---

## 🎯 Resumen Rápido

| Acción | Comando/Pasos |
|--------|---------------|
| **Inicio manual** | Doble clic en `IniciarEGRESAPP2.bat` |
| **Acceso directo** | Clic derecho → Enviar a → Escritorio |
| **Inicio automático** | Copiar `.bat` a carpeta de inicio (`shell:startup`) |
| **URL de la plataforma** | `http://localhost/EGRESAPP2/index.php` |

---

¿Necesitas ayuda? Revisa la sección de **Solución de Problemas** o contacta al administrador del sistema.
