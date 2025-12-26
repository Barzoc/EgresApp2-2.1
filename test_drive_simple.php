<?php
require_once __DIR__ . '/lib/GoogleDriveClient.php';

try {
    echo "Iniciando prueba de conexión con Service Account...\n";
    $drive = new GoogleDriveClient();
    
    if ($drive->isEnabled()) {
        echo "Cliente Drive habilitado.\n";
        echo "Intentando listar archivos...\n";
        $files = $drive->listFiles();
        echo "Conexión EXITOSA. Archivos encontrados: " . count($files) . "\n";
        print_r(array_slice($files, 0, 5)); // Mostrar primeros 5
    } else {
        echo "Drive no está habilitado en la configuración.\n";
    }

} catch (Exception $e) {
    echo "ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
