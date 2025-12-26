<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Información de la tabla expediente_queue ===\n\n";

// Ver estructura de la tabla
$sql = "DESCRIBE expediente_queue";
try {
    $query = $db->query($sql);
    $columns = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estructura de la tabla:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
    }
    
    // Ver cuántos registros tiene
    $sql = "SELECT COUNT(*) as total FROM expediente_queue";
    $query = $db->query($sql);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal de registros: {$result['total']}\n";
    
    if ($result['total'] > 0) {
        echo "\nPrimeros 5 registros:\n";
        $sql = "SELECT * FROM expediente_queue LIMIT 5";
        $query = $db->query($sql);
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    }
    
    echo "\n=== Conclusión ===\n";
    if ($result['total'] == 0) {
        echo "La tabla está VACÍA y NO se usa en el código.\n";
        echo "Es seguro eliminarla.\n\n";
        echo "Comando para eliminar:\n";
        echo "DROP TABLE `expediente_queue`;\n";
    } else {
        echo "La tabla tiene {$result['total']} registros.\n";
        echo "Revisa los datos antes de eliminarla.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
