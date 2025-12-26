<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $stmt = $pdo->query("SELECT identificacion, nombrecompleto, expediente_pdf FROM egresado WHERE identificacion IN (1, 2)");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        echo "ID: " . $row['identificacion'] . "\n";
        echo "PDF: " . $row['expediente_pdf'] . "\n";
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
