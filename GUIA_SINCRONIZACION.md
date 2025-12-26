# Guía de Sincronización EGRESAPP2 (PC1 → PC2)

Esta guía explica cómo transferir la configuración completa, credenciales y base de datos de EGRESAPP2 del Computador 1 al Computador 2.

## Requisitos
- **PC1 (Origen)**: Sistema funcionando correctamente.
- **PC2 (Destino)**: Debe tener Laragon instalado.

## Paso 1: Generar Paquete en PC1
1. En el script de Laragon o carpeta del proyecto, busca y ejecuta el archivo:
   `SINCRONIZAR_COMPLETO.bat`
2. Espera a que termine. Se abrirá automáticamente una carpeta llamada `EGRESAPP2_SYNC_PACKAGE` (ubicada en `C:\`).
3. Esta carpeta contiene todo lo necesario (Configuración, SQL, Scripts).

## Paso 2: Transferir a PC2
1. Conecta una memoria USB al PC1.
2. Copia la carpeta `EGRESAPP2_SYNC_PACKAGE` a la memoria USB.
3. Conecta la USB al **PC2**.
4. Copia la carpeta de la USB al Escritorio (o cualquier lugar accesible) del PC2.

## Paso 3: Aplicar en PC2
1. En el PC2, abre la carpeta `EGRESAPP2_SYNC_PACKAGE` que acabas de copiar.
2. Haz doble clic en el archivo:
   **`APLICAR_SINCRONIZACION.bat`**
3. Se abrirá una ventana negra que realizará todo el proceso automáticamente:
   - Copiará los archivos de configuración.
   - Copiará las credenciales de Google Drive.
   - Importará la base de datos actualizada.
   - Ajustará las rutas del sistema para el nuevo PC.

## Verificación
Una vez termine el proceso en PC2:
1. Reinicia Laragon en PC2 (Botón "Stop" y luego "Start All").
2. Abre el navegador en `http://localhost/EGRESAPP2`.
3. Ingresa con tu usuario y contraseña (son los mismos del PC1).
4. Verifica que puedas ver los expedientes y que la conexión a Google Drive funcione.

## Solución de Problemas
- **"No se encuentra la base de datos"**: Asegúrate de que MySQL esté corriendo en Laragon en PC2 (semáforo en verde).
- **"Error de Acceso Denegado"**: Ejecuta `APLICAR_SINCRONIZACION.bat` como Administrador.

> [!WARNING]
> La carpeta de sincronización contiene **Credenciales de Acceso**.
> Una vez hayas configurado el PC2, elimina la carpeta `EGRESAPP2_SYNC_PACKAGE` de la USB y del Escritorio por seguridad.
