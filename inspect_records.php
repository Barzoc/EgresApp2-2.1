<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // Get first 5 records to inspect
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado ORDER BY identificacion ASC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        echo "ID: " . $row['identificacion'] . "\n";
        echo "Nombre: " . $row['nombreCompleto'] . "\n";
        echo "PDF (Raw): " . json_encode($row['expediente_pdf']) . "\n"; // Use json_encode to see hidden characters
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
