<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Eliminando Foreign Key ===\n\n";

try {
    $sql = "ALTER TABLE `tituloegresado` DROP FOREIGN KEY `fk_tituloegresado_egresado1`";
    $db->exec($sql);
    echo "✓ Foreign key 'fk_tituloegresado_egresado1' eliminada exitosamente\n\n";
    
    echo "=== Probando eliminación de egresado ===\n";
    $id = '1';
    
    $db->beginTransaction();
    
    // Eliminar de tituloegresado primero
    $sql = "DELETE FROM tituloegresado WHERE identificacion = :id";
    $query = $db->prepare($sql);
    $query->execute([':id' => $id]);
    echo "✓ Eliminado de tituloegresado\n";
    
    // Eliminar de egresado
    $sql = "DELETE FROM egresado WHERE identificacion = :id";
    $query = $db->prepare($sql);
    $query->execute([':id' => $id]);
    echo "✓ Eliminado de egresado\n";
    
    $db->rollBack(); // Rollback para no eliminar realmente
    echo "\n✓ PRUEBA EXITOSA (rollback aplicado, no se eliminó nada)\n";
    echo "\nAhora puedes eliminar egresados desde la plataforma sin problemas.\n";
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
