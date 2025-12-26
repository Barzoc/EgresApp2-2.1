# Guía rápida para instalar EgresApp2 en otro PC

Sigue estos pasos en orden para clonar la plataforma con la misma configuración.

---
## 1. Preparar el respaldo (PC origen)
1. **Respaldar la base de datos**
   ```powershell
   $timestamp = Get-Date -Format "yyyyMMdd_HHmm"
   & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe" \
     -u root \
     --databases gestion_egresados \
     --routines --events --single-transaction --default-character-set=utf8mb4 |
     Out-File -Encoding UTF8 "C:\Respaldos\gestion_egresados_$timestamp.sql"
   ```
   - Ajusta la ruta si tu versión de MySQL es distinta.
   - Si tu usuario `root` tiene contraseña, agrega `-p`.

2. **Respaldar los archivos del sitio**
   - Copia la carpeta completa `C:\laragon\www\EGRESAPP2`.
   - Incluye carpetas con archivos subidos (`assets/expedientes/expedientes_subidos/`, etc.).

3. **Guardar credenciales/configuraciones**
   - Usuarios y contraseñas de la app.
   - Cualquier dato de `.env`, APIs o claves de Google Drive.

---
## 2. Preparar el nuevo PC
1. **Instalar Laragon (o XAMPP)**
   - Descarga Laragon (64 bits) y selecciónalo como stack principal.
   - Acepta Apache + MySQL/MariaDB.

2. **Copiar la aplicación**
   - Pega `EGRESAPP2` dentro de `C:\laragon\www\` (reemplaza si existe).

3. **Restaurar la base de datos**
   ```powershell
   "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root < "C:\Respaldos\gestion_egresados_YYYYMMDD_HHMM.sql"
   ```
   - Cambia el nombre del archivo `.sql` por el que generaste.
   - Si `root` tiene contraseña, agrega `-p`.

4. **Configurar conexión**
   - Verifica los parámetros en tu archivo de conexión (probablemente `modelo/Conexion.php`).
   - Confirma que host, usuario y contraseña coinciden con el nuevo stack.

5. **Re-crear carpetas subidas (si es necesario)**
   - Asegúrate de que existan las rutas `assets/expedientes/expedientes_subidos/` y otras carpetas de almacenamiento.
   - Verifica permisos de lectura/escritura.

---
## 3. Verificación
1. Inicia Laragon (Apache + MySQL).
2. Abre el navegador en `http://localhost/EGRESAPP2/vista/adm_egresado.php`.
3. Confirma:
   - El panel "Firmante titular" carga sin errores.
   - La tabla de egresados muestra registros.
   - Los modales y acciones AJAX (crear, importar, firmante) funcionan.
4. Si ves errores de permisos o archivos faltantes, revisa el log `storage/logs` o `php_error.log` en Laragon.

---
## 4. Restauración de Google Drive / APIs (opcional)
- Si usas integraciones con Drive, copia los archivos de configuración (credenciales JSON, tokens) o vuelve a generarlos.
- Verifica que los ID de carpetas en `assets/js/egresado.js` o en tus controladores sigan siendo válidos.

---
## 5. Tips
- Mantén la carpeta `C:\Respaldos` con fecha para futuras migraciones.
- Antes de migrar nuevamente, repite el proceso de dump para capturar datos recientes.
- Para automatizar, crea un script `.ps1` que ejecute el dump, comprima la carpeta del sitio y copie ambos archivos.

Con esta guía tendrás EgresApp2 funcionando igual que en el equipo original.
