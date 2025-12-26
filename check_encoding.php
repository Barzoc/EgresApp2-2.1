<?php
// check_encoding.php
require_once 'modelo/Conexion.php';

echo "<h1>Verificación de Codificación</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Check table encoding
    echo "<h2>1. Codificación de la tabla 'egresado'</h2>";
    $stmt = $pdo->query("SHOW CREATE TABLE egresado");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($result);
    echo "</pre>";

    // 2. Check specific record
    echo "<h2>2. Datos del primer egresado</h2>";
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, tituloObtenido, HEX(nombreCompleto) as hex_nombre FROM egresado LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($row);
    echo "</pre>";

    // 3. Check connection encoding
    echo "<h2>3. Variables de conexión</h2>";
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
    $vars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($vars);
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>