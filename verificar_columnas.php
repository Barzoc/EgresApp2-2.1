<?php
// Verificar nombres reales de columnas en la tabla egresado
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICACIÓN DE COLUMNAS ===\n\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_egresados;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Columnas en la tabla 'egresado':\n\n";
    
    $stmt = $pdo->query("DESCRIBE egresado");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
