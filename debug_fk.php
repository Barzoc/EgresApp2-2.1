<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Verificando Foreign Keys ===\n\n";

// Buscar todas las FK que referencian a egresado
$sql = "SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = 'gestion_egresados'
        AND REFERENCED_TABLE_NAME = 'egresado'";

$query = $db->prepare($sql);
$query->execute();
$fks = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($fks)) {
    echo "✓ No hay foreign keys apuntando a 'egresado'\n";
} else {
    echo "✗ Se encontraron las siguientes foreign keys:\n\n";
    foreach ($fks as $fk) {
        echo "Tabla: {$fk['TABLE_NAME']}\n";
        echo "  Columna: {$fk['COLUMN_NAME']}\n";
        echo "  FK Name: {$fk['CONSTRAINT_NAME']}\n";
        echo "  Referencia: egresado.{$fk['REFERENCED_COLUMN_NAME']}\n\n";
    }
    
    echo "\n=== Comandos para eliminar las FK ===\n\n";
    foreach ($fks as $fk) {
        echo "ALTER TABLE `{$fk['TABLE_NAME']}` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`;\n";
    }
}

echo "\n=== Intentando eliminar ID 1 (ADRIAN) ===\n";
$id = '1';
try {
    $db->beginTransaction();
    
    $sql = "DELETE FROM egresado WHERE identificacion = :id";
    $query = $db->prepare($sql);
    $query->execute([':id' => $id]);
    echo "✓ Eliminación exitosa\n";
    
    $db->rollBack(); // Rollback para no eliminar realmente
    echo "✓ PRUEBA EXITOSA (rollback aplicado)\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
