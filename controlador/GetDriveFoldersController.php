<?php
/**
 * Controlador para obtener carpetas de Google Drive
 * Retorna el mapeo de carpetas definido en config/drive_folders.php
 */

require_once __DIR__ . '/../config/drive_folders.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $folders = require __DIR__ . '/../config/drive_folders.php';
    
    if (!is_array($folders)) {
        throw new Exception('El archivo drive_folders.php no retorna un array válido');
    }
    
    $result = [];
    foreach ($folders as $folder) {
        // Obtener el primer alias como nombre principal
        $name = isset($folder['aliases'][0]) ? $folder['aliases'][0] : 'Sin nombre';
        $folderId = $folder['drive_folder_id'] ?? null;
        
        if (!$folderId) {
            continue; // Saltar carpetas sin ID
        }
        
        $result[] = [
            'name' => $name,
            'folder_id' => $folderId,
            'all_aliases' => $folder['aliases'] ?? []
        ];
    }
    
    echo json_encode([
        'success' => true,
        'carpetas' => $result
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al obtener carpetas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
