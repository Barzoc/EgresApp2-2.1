<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // Count total records
    $stmt = $pdo->query("SELECT COUNT(*) FROM expediente_queue");
    $total = $stmt->fetchColumn();

    // Count by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM expediente_queue GROUP BY status");
    $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get oldest pending/failed records
    $stmt = $pdo->query("SELECT * FROM expediente_queue ORDER BY created_at ASC LIMIT 10");
    $oldest = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total records: $total\n\n";
    echo "By Status:\n";
    foreach ($byStatus as $row) {
        echo "{$row['status']}: {$row['count']}\n";
    }

    echo "\nOldest 10 records:\n";
    foreach ($oldest as $row) {
        echo "ID: {$row['id']}, Status: {$row['status']}, Created: {$row['created_at']}, File: {$row['filename']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
