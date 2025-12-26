<?php
// diagnostico_login.php
require_once 'modelo/Conexion.php';

echo "<h1>Diagnostico de Login</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Check Table Columns
    echo "<h2>1. Columnas de la tabla 'usuario'</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    $passCol = null;
    foreach ($columns as $col) {
        // Handle case sensitivity from PDO::ATTR_CASE => PDO::CASE_LOWER
        $field = strtolower($col['field'] ?? $col['Field']);
        if (strpos($field, 'pass') !== false || strpos($field, 'contra') !== false) {
            $passCol = $col['field'] ?? $col['Field']; // Keep original case for query if needed, though MySQL is usually case-insensitive for cols
        }
    }
    echo "Columna de contraseña detectada: " . ($passCol ? $passCol : "NINGUNA") . "<br>";

    // 2. Check Admin User
    echo "<h2>2. Usuario admin@test.com</h2>";
    $sql = "SELECT * FROM usuario WHERE email = 'admin@test.com'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Usuario encontrado.<br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";

        if ($passCol) {
            $hash = $user[$passCol] ?? $user[strtolower($passCol)];
            echo "Hash en DB: " . substr($hash, 0, 10) . "...<br>";

            $verify = password_verify('12345678', $hash);
            echo "password_verify('12345678', hash): " . ($verify ? "<b style='color:green'>TRUE</b>" : "<b style='color:red'>FALSE</b>") . "<br>";

            // Try generating a new hash to compare
            $newHash = password_hash('12345678', PASSWORD_BCRYPT);
            echo "Nuevo hash generado para '12345678': " . substr($newHash, 0, 10) . "...<br>";
        } else {
            echo "No se puede verificar password porque no se detectó la columna.<br>";
        }

    } else {
        echo "<b style='color:red'>Usuario admin@test.com NO encontrado.</b><br>";

        // List all users
        echo "<h3>Usuarios existentes:</h3>";
        $stmt = $pdo->query("SELECT * FROM usuario LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($users);
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage();
}
?>