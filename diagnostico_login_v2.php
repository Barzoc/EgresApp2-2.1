<?php
// diagnostico_login_v2.php
require_once 'modelo/Conexion.php';

echo "<h1>Diagnostico de Login V2</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    echo "<h2>Usuario admin@test.com</h2>";
    $sql = "SELECT * FROM usuario WHERE email = 'admin@test.com'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Usuario encontrado. Keys del array:<br>";
        echo "<pre>";
        foreach ($user as $key => $val) {
            echo "Key: [" . $key . "] (len: " . strlen($key) . ") - Value len: " . strlen($val) . "\n";
            // Hex dump key to see hidden chars or encoding issues
            echo "Hex Key: " . bin2hex($key) . "\n";
        }
        echo "</pre>";

        // Check specific access
        $keyToTry = 'contraseña';
        echo "Accediendo a \$user['$keyToTry']: ";
        if (isset($user[$keyToTry])) {
            echo "EXISTE. Hash: " . substr($user[$keyToTry], 0, 10) . "...\n";
        } else {
            echo "NO EXISTE.\n";
        }

    } else {
        echo "Usuario no encontrado.";
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage();
}
?>