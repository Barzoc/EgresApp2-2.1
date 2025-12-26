<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Análisis de la tabla tituloegresado ===\n\n";

// Ver estructura
$sql = "DESCRIBE tituloegresado";
$query = $db->query($sql);
$columns = $query->fetchAll(PDO::FETCH_ASSOC);

echo "Estructura:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

// Ver cuántos registros
$sql = "SELECT COUNT(*) as total FROM tituloegresado";
$query = $db->query($sql);
$result = $query->fetch(PDO::FETCH_ASSOC);
echo "\nTotal de registros: {$result['total']}\n";

// Ver algunos registros
if ($result['total'] > 0) {
    echo "\nPrimeros 5 registros:\n";
    $sql = "SELECT te.*, t.nombre as titulo_nombre 
            FROM tituloegresado te 
            LEFT JOIN titulo t ON te.id = t.id 
            LIMIT 5";
    $query = $db->query($sql);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "\n  ID Egresado: {$row['identificacion']}\n";
        echo "  Título ID: {$row['id']}\n";
        echo "  Título: {$row['titulo_nombre']}\n";
        echo "  Fecha Grado: {$row['fechaGrado']}\n";
    }
}

echo "\n=== Uso en el código ===\n";
echo "La tabla 'tituloegresado' se usa en:\n";
echo "  - Egresado.php: LEFT JOIN para obtener fechaGrado\n";
echo "  - ExpedienteStorageController.php: Para obtener título y mapear carpetas\n";
echo "  - EgresadoController.php: Para obtener título en subida manual\n";
echo "  - Función Eliminar: Se elimina primero antes de eliminar egresado\n\n";

echo "=== Conclusión ===\n";
echo "La tabla 'tituloegresado' SÍ se usa activamente.\n";
echo "NO debe eliminarse.\n";
echo "Almacena la relación entre egresados y títulos obtenidos.\n";
