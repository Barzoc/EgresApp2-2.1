<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // Check column names
    $stmt = $pdo->query("SELECT * FROM egresado LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r(array_keys($row));

    echo "\n\nRe-inspecting first 5 records:\n";
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado ORDER BY identificacion ASC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        echo "ID: " . $row['identificacion'] . "\n";
        // Handle potential case sensitivity or column name mismatch
        $nombre = $row['nombreCompleto'] ?? $row['nombrecompleto'] ?? 'N/A';
        echo "Nombre: " . $nombre . "\n";
        echo "PDF: " . json_encode($row['expediente_pdf']) . "\n";
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
