<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // Fix the specific corrupted record
    $stmt = $pdo->prepare("UPDATE egresado SET expediente_pdf = 'ADRIAN_VICTOR_ANDRES_YA__EZ_ROJAS__000013.pdf' WHERE identificacion = 1");
    $stmt->execute();

    echo "Record updated successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
