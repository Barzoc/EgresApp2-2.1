<?php
// Set proper headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'modelo/Conexion.php';

try {
    $db = new Conexion();
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'pdo' => $db->pdo !== null ? 'PDO object created' : 'PDO is null'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>