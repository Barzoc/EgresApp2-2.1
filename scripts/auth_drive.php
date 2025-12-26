<?php
require_once __DIR__ . '/../lib/GoogleDriveClient.php';

echo "Iniciando proceso de autenticación...\n";

$tokenPath = __DIR__ . '/../config/token.json';
if (file_exists($tokenPath)) {
    echo "Eliminando token expirado/inválido...\n";
    unlink($tokenPath);
}

try {
    // Forzar la creación del cliente. Al no haber token, pedirá uno nuevo.
    $client = new GoogleDriveClient();

    if ($client->isEnabled()) {
        echo "Autenticación exitosa. El token es válido.\n";
    } else {
        echo "Error: Google Drive no está habilitado.\n";
    }

} catch (Throwable $e) {
    echo "Error fatal: " . $e->getMessage() . "\n";
}
