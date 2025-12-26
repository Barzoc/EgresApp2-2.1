<?php
require __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;
    
    // Search for the user mentioned
    $namePart = "ADRIAN"; 
    $sql = "SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado WHERE nombreCompleto LIKE :name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => "%$namePart%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Results for '$namePart':\n";
    foreach ($results as $r) {
        echo "ID: " . $r['identificacion'] . "\n";
        echo "Nombre: " . $r['nombreCompleto'] . "\n";
        echo "Expediente PDF (DB): '" . $r['expediente_pdf'] . "'\n";
        
        $baseDir = __DIR__ . '/assets/expedientes/expedientes_subidos';
        $fullPath = $baseDir . '/' . $r['expediente_pdf'];
        echo "Full Path Check: " . $fullPath . "\n";
        echo "Exists? " . (file_exists($fullPath) ? "YES" : "NO") . "\n";
        echo "-------------------\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
