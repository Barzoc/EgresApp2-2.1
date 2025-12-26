<?php
// dump_raw_db_data.php
require_once 'modelo/Conexion.php';

echo "<h1>Análisis de Datos Crudos de la BD</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, tituloObtenido FROM egresado LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre (Visual)</th><th>Nombre (Hex)</th><th>Título (Visual)</th><th>Título (Hex)</th></tr>";

    foreach ($rows as $row) {
        // Use lowercase keys as per PDO::CASE_LOWER
        $nombre = $row['nombrecompleto'] ?? $row['nombreCompleto'] ?? '';
        $titulo = $row['tituloobtenido'] ?? $row['tituloObtenido'] ?? '';

        echo "<tr>";
        echo "<td>" . ($row['identificacion'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($nombre) . "</td>";
        echo "<td>" . bin2hex($nombre) . "</td>";
        echo "<td>" . htmlspecialchars($titulo) . "</td>";
        echo "<td>" . bin2hex($titulo) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Análisis de Hex</h2>";
    echo "<ul>";
    echo "<li><b>C3 81</b> = Á (UTF-8)</li>";
    echo "<li><b>C3 89</b> = É (UTF-8)</li>";
    echo "<li><b>C3 8D</b> = Í (UTF-8)</li>";
    echo "<li><b>C3 93</b> = Ó (UTF-8)</li>";
    echo "<li><b>C3 9A</b> = Ú (UTF-8)</li>";
    echo "<li><b>C3 91</b> = Ñ (UTF-8)</li>";
    echo "<li><b>3F</b> = ? (ASCII)</li>";
    echo "<li><b>EF BF BD</b> =  (Replacement Character)</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>