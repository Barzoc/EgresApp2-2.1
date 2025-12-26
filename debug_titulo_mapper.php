<?php
require_once __DIR__ . '/lib/DriveFolderMapper.php';
require_once __DIR__ . '/modelo/Conexion.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conexion = new Conexion();
    $db = $conexion->pdo;
    
    echo "=== DEBUG MAPPER DE TITULOS ===\n";
    echo "Analizando ultimos 5 expedientes...\n\n";

    // Obtener ultimos egresados
    $stmt = $db->query("SELECT identificacion, nombreCompleto, tituloObtenido, expediente_drive_id FROM egresado ORDER BY identificacion DESC LIMIT 5");
    $egresados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($egresados as $row) {
        $tituloRaw = $row['tituloobtenido'] ?? $row['tituloObtenido'] ?? ''; // Try both just in case
        echo "--------------------------------------------------\n";
        echo "ID: " . ($row['identificacion'] ?? 'N/A') . "\n";
        echo "Nombre: " . ($row['nombrecompleto'] ?? $row['nombreCompleto'] ?? 'N/A') . "\n";
        echo "Titulo RAW (DB): [" . $tituloRaw . "]\n";
        
        // Simular lógica interna de DriveFolderMapper
        // Como normalizeKey es privado, lo replicamos o usamos reflection, 
        // pero mejor confiamos en el resultado de resolveByTitle.
        
        $resolucion = DriveFolderMapper::resolveByTitle($tituloRaw);
        
        echo "Mapeo Resultado:\n";
        if (!empty($resolucion['drive_folder_id'])) {
            echo "  [OK] Drive Folder ID: " . $resolucion['drive_folder_id'] . "\n";
            echo "  [OK] Carpeta Local: " . $resolucion['local_folder'] . "\n";
            echo "  [OK] Label: " . $resolucion['label'] . "\n";
        } else {
            echo "  [FAIL] No se encontró coincidencia (Usará ROOT).\n";
        }
        
        echo "Drive ID Actual en DB: " . ($row['expediente_drive_id'] ?? 'NULL') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
