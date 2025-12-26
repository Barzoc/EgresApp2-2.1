<?php
/**
 * Test rápido de Google Drive API
 * Verifica si el token funciona y lista archivos
 */

require_once __DIR__ . '/lib/GoogleDriveClient.php';

echo "========================================\n";
echo "   TEST DE CONEXIÓN GOOGLE DRIVE\n";
echo "========================================\n\n";

try {
    echo "🔄 Inicializando cliente de Google Drive...\n";
    $driveClient = new GoogleDriveClient();
    
    if (!$driveClient->isEnabled()) {
        echo "❌ ERROR: Google Drive no está habilitado.\n";
        echo "   Verifica config/drive.php\n";
        exit(1);
    }
    
    echo "✅ Cliente inicializado correctamente\n\n";
    
    echo "🔄 Obteniendo ID de carpeta raíz...\n";
    $rootFolderId = $driveClient->getRootFolderId();
    echo "📁 Carpeta raíz: $rootFolderId\n\n";
    
    echo "🔄 Listando archivos en Google Drive...\n";
    $files = $driveClient->listFiles();
    
    if (empty($files)) {
        echo "ℹ️  No hay archivos en la carpeta raíz de Drive.\n";
    } else {
        echo "✅ Encontrados " . count($files) . " archivos:\n\n";
        
        $count = 0;
        foreach ($files as $fileName => $fileId) {
            $count++;
            echo "  $count. $fileName (ID: $fileId)\n";
            
            // Mostrar solo los primeros 10
            if ($count >= 10) {
                $remaining = count($files) - 10;
                if ($remaining > 0) {
                    echo "  ... y $remaining archivos más\n";
                }
                break;
            }
        }
    }
    
    echo "\n========================================\n";
    echo "✅ TEST EXITOSO\n";
    echo "========================================\n";
    echo "Google Drive está funcionando correctamente.\n";
    echo "Puedes subir y descargar expedientes.\n\n";
    
} catch (RuntimeException $e) {
    echo "\n========================================\n";
    echo "❌ ERROR DE AUTENTICACIÓN\n";
    echo "========================================\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'autorización OAuth') !== false) {
        echo "💡 SOLUCIÓN:\n";
        echo "   1. Ejecuta: RENOVAR_TOKEN_DRIVE.bat\n";
        echo "   2. Autoriza con tu cuenta de Google\n";
        echo "   3. Ejecuta este test nuevamente\n\n";
    }
    
    exit(1);
    
} catch (Exception $e) {
    echo "\n========================================\n";
    echo "❌ ERROR INESPERADO\n";
    echo "========================================\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}
