<?php
require_once __DIR__ . '/modelo/Conexion.php';
$db = new Conexion();
$stmt = $db->pdo->query('SELECT identificacion, carnet, nombreCompleto, expediente_pdf FROM egresado LIMIT 10');
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($data);
