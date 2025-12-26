<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $stmt = $pdo->prepare("SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado WHERE nombreCompleto LIKE :nombre");
    $stmt->execute([':nombre' => '%ADRIAN VICTOR%']);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($result);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
