<?php
require __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;
    $stmt = $pdo->query("SHOW COLUMNS FROM egresado");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in egresado table:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
