<?php
// configurar_firmante_default.php
require_once 'modelo/Conexion.php';

echo "<h1>Configurando Firmante por Defecto</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $nombre = "CAROLINA HIDALGO SOFJER";
    $cargo = "RECTORA";

    // Usar INSERT ... ON DUPLICATE KEY UPDATE para insertar o actualizar
    $sql = "INSERT INTO configuracion_certificado (clave, valor) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['firmante_nombre', $nombre]);
    $stmt->execute(['firmante_cargo', $cargo]);

    echo "<b style='color:green'>✓ Firmante configurado correctamente:</b><br>";
    echo "Nombre: <b>$nombre</b><br>";
    echo "Cargo: <b>$cargo</b><br><br>";

    // Verificar
    $stmt = $pdo->query("SELECT * FROM configuracion_certificado WHERE clave LIKE 'firmante%'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Datos guardados en la base de datos:</h3>";
    echo "<pre>";
    print_r($rows);
    echo "</pre>";

} catch (Exception $e) {
    echo "<b style='color:red'>Error: " . $e->getMessage() . "</b>";
}
?>