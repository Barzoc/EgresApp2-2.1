<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Eliminando tablas legacy: tituloegresado y titulo ===\n\n";

try {
    // 1. Eliminar tituloegresado (relación)
    $sql = "DROP TABLE IF EXISTS `tituloegresado`";
    $db->exec($sql);
    echo "✓ Tabla 'tituloegresado' eliminada exitosamente\n";
    
    // 2. Eliminar titulo (catálogo)
    $sql = "DROP TABLE IF EXISTS `titulo`";
    $db->exec($sql);
    echo "✓ Tabla 'titulo' eliminada exitosamente\n\n";
    
    echo "Base de datos simplificada y optimizada.\n";
    echo "Ahora todos los datos se manejan directamente en la tabla 'egresado'.\n";
    
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
