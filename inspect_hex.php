<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $stmt = $pdo->query("SELECT identificacion, nombrecompleto, expediente_pdf FROM egresado ORDER BY identificacion ASC LIMIT 2");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        echo "ID: " . $row['identificacion'] . "\n";
        echo "Nombre: " . $row['nombrecompleto'] . "\n";
        $pdf = $row['expediente_pdf'];
        echo "PDF String: " . $pdf . "\n";
        echo "PDF Hex: " . bin2hex($pdf) . "\n";
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
