<?php
require_once __DIR__ . '/../modelo/Conexion.php';
$db = new Conexion();
$sql = "SELECT identificacion, nombreCompleto, expediente_pdf FROM egresado WHERE nombreCompleto LIKE '%Claudio Alexis Ardiles%'";
$stmt = $db->pdo->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
