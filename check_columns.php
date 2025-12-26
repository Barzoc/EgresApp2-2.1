<?php
// check_columns.php
require_once 'modelo/Conexion.php';

$db = new Conexion();
$pdo = $db->pdo;

echo "<h2>Columnas de la tabla 'egresado'</h2>";
$stmt = $pdo->query("SHOW COLUMNS FROM egresado");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
foreach ($columns as $col) {
    $col = array_change_key_case($col, CASE_LOWER);
    echo $col['field'] . "\n";
}
echo "</pre>";

echo "<h2>Primer registro de ejemplo</h2>";
$stmt = $pdo->query("SELECT * FROM egresado LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r(array_keys($row));
echo "</pre>";
?>