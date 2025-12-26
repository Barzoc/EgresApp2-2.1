<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Eliminando tabla expediente_queue ===\n\n";

try {
    // Primero verificar que existe
    $sql = "SHOW TABLES LIKE 'expediente_queue'";
    $result = $db->query($sql);
    
    if ($result->rowCount() > 0) {
        // Eliminar la tabla
        $sql = "DROP TABLE `expediente_queue`";
        $db->exec($sql);
        echo "✓ Tabla 'expediente_queue' eliminada exitosamente\n\n";
        echo "La tabla era parte de un sistema OCR antiguo que ya no se usa.\n";
        echo "Contenía 20 registros de procesamiento de PDFs anteriores.\n\n";
        echo "Base de datos limpiada correctamente.\n";
    } else {
        echo "✓ La tabla 'expediente_queue' ya no existe.\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Tablas actuales en la base de datos ===\n";
$sql = "SHOW TABLES";
$result = $db->query($sql);
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "  - $table\n";
}
