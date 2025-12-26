<?php

/**
 * Ejecuta la sincronización con Google Drive en segundo plano.
 * Este helper se puede llamar después de subir archivos para mantener
 * Drive sincronizado automáticamente.
 */
function triggerDriveSync(): void
{
    $scriptPath = __DIR__ . '/../scripts/sync_drive.php';
    
    if (!is_file($scriptPath)) {
        error_log('DriveSync: Script no encontrado en ' . $scriptPath);
        return;
    }

    // Ejecutar en segundo plano sin bloquear la respuesta
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $cmd = sprintf(
            'start /B php "%s" > nul 2>&1',
            $scriptPath
        );
        pclose(popen($cmd, 'r'));
    } else {
        // Linux/Mac
        $cmd = sprintf(
            'php "%s" > /dev/null 2>&1 &',
            $scriptPath
        );
        exec($cmd);
    }
    
    error_log('DriveSync: Sincronización iniciada en segundo plano');
}
