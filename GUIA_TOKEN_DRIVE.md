# 🔑 Guía: Renovar Token de Google Drive

## 📋 Problema

Después de **7 días**, el token de autorización de Google Drive expira y el sistema no puede:
- ❌ Subir expedientes a Google Drive
- ❌ Sincronizar expedientes desde Drive
- ❌ Acceder a las carpetas de Drive

## 🎯 Solución Rápida (5 minutos)

### Opción 1: Usar Script Automático (Recomendado) ⭐

1. **Ejecuta**:
   ```
   RENOVAR_TOKEN_DRIVE.bat
   ```

2. **Sigue las instrucciones**:
   - Se abrirá un enlace en el navegador
   - Inicia sesión con tu cuenta de Google
   - Autoriza la aplicación
   - Copia el código que aparece
   - Pégalo en la terminal

3. **¡Listo!** El token se renovó automáticamente.

---

### Opción 2: Método Manual

1. **Elimina el token antiguo**:
   ```cmd
   del config\token.json
   ```

2. **Ejecuta el script de autorización**:
   ```cmd
   php scripts\authorize_drive.php
   ```

3. **Sigue las instrucciones** como en la Opción 1.

---

## 🔍 Verificar Estado del Token

Antes de renovar, puedes verificar si realmente necesitas hacerlo:

```cmd
VERIFICAR_TOKEN_DRIVE.bat
```

El script te mostrará:
- ✅ **VÁLIDO** → No necesitas hacer nada
- ⚠️ **ADVERTENCIA** → Renueva pronto (quedan pocas horas)
- ❌ **EXPIRADO** → Debes renovar AHORA

---

## ❓ ¿Por Qué Expira el Token?

El sistema usa una **aplicación de prueba** en Google Cloud, que tiene limitaciones:

- ✅ **Access Token**: Se renueva automáticamente (dura 1 hora)
- ⚠️ **Refresh Token**: Expira después de **7 días** en modo prueba

### Solución Permanente: Publicar la App

Para evitar renovar cada 7 días, puedes publicar la app en Google Cloud:

1. Ve a [Google Cloud Console](https://console.cloud.google.com)
2. Selecciona tu proyecto: `hip-orbit-458817-b4`
3. Ve a **APIs & Services** → **OAuth consent screen**
4. Cambia el estado a **"En producción"**
5. Completa el proceso de verificación

> ⚠️ **NOTA**: Esto requiere verificación de Google (puede tomar días).  
> Mientras tanto, usa `RENOVAR_TOKEN_DRIVE.bat` cada 7 días.

---

## 🔧 Solución de Problemas

### ❌ Error: "Invalid grant"

**Causa**: El código de autorización ya fue usado o expiró.

**Solución**:
1. Ejecuta nuevamente `RENOVAR_TOKEN_DRIVE.bat`
2. Solicita un nuevo código (no reutilices el anterior)

---

### ❌ Error: "redirect_uri_mismatch"

**Causa**: La configuración de OAuth no coincide.

**Solución**:
1. Ve a [Google Cloud Console](https://console.cloud.google.com)
2. Verifica que `urn:ietf:wg:oauth:2.0:oob` esté en la lista de URIs autorizadas

---

### ❌ No se abre el navegador

**Solución**:
1. Copia manualmente el enlace que aparece en la terminal
2. Pégalo en tu navegador
3. Continúa con el proceso

---

## 📅 Calendario de Renovación

Si decides no publicar la app, programa renovaciones:

| Fecha Primera Autorización | Próxima Renovación |
|----------------------------|-------------------|
| 2024-12-11 | 2024-12-18 |
| 2024-12-18 | 2024-12-25 |
| 2024-12-25 | 2025-01-01 |

> 💡 **TIP**: Configura un recordatorio en tu calendario cada 6 días.

---

## 🤖 Automatización (Avanzado)

Si quieres automatizar la renovación cada 6 días:

```powershell
# Programar tarea en Windows
schtasks /create /tn "Renovar Token Drive" /tr "C:\laragon\www\EGRESAPP2\RENOVAR_TOKEN_DRIVE.bat" /sc weekly /d MON,THU /st 09:00
```

> ⚠️ **ADVERTENCIA**: Esto aún requiere interacción manual (autorizar en el navegador).

---

## 📞 Resumen de Comandos

| Acción | Comando |
|--------|---------|
| **Ver estado del token** | `VERIFICAR_TOKEN_DRIVE.bat` |
| **Renovar token** | `RENOVAR_TOKEN_DRIVE.bat` |
| **Renovar manualmente** | `php scripts\authorize_drive.php` |
| **Eliminar token** | `del config\token.json` |

---

## ✅ Checklist de Renovación

- [ ] Verificar que el token está expirado (`VERIFICAR_TOKEN_DRIVE.bat`)
- [ ] Ejecutar `RENOVAR_TOKEN_DRIVE.bat`
- [ ] Iniciar sesión en Google
- [ ] Autorizar la aplicación
- [ ] Copiar el código de autorización
- [ ] Pegar el código en la terminal
- [ ] Verificar mensaje "¡Autorización exitosa!"
- [ ] Probar subiendo un expediente de prueba

---

## 🎉 Verificación Final

Después de renovar, verifica que funciona:

1. **Abre EGRESAPP2** en el navegador
2. **Sube un expediente de prueba**
3. **Verifica** que aparece en Google Drive
4. ✅ Si funciona, ¡todo está correcto!

---

## 📞 Soporte

Si tienes problemas:

1. Ejecuta `VERIFICAR_TOKEN_DRIVE.bat` y revisa el estado
2. Revisa los errores en: `logs/database.log`
3. Verifica que `config/client_secret.json` existe
4. Verifica conexión a Internet

---

**Fecha de creación**: 2024-12-18  
**Última actualización**: 2024-12-18
