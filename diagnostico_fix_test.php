<?php
// diagnostico_fix_test.php

$host = 'localhost';
$dbname = 'gestion_egresados';
$user = 'root';
$pass = '';

function test_connection($label, $options, $post_commands = [])
{
    global $host, $dbname, $user, $pass;
    echo "<h3>Test: $label</h3>";
    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, $options);

        foreach ($post_commands as $cmd) {
            $pdo->exec($cmd);
        }

        $stmt = $pdo->query("SELECT * FROM usuario LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo "Keys found: ";
            foreach ($row as $key => $val) {
                if (strpos($key, 'contra') !== false || strpos($key, 'pass') !== false) {
                    echo "[$key] (" . bin2hex($key) . ") ";
                }
            }
            echo "<br>";

            $target = 'contraseña';
            if (isset($row[$target])) {
                echo "<b style='color:green'>SUCCESS: Key '$target' found.</b><br>";
            } else {
                echo "<b style='color:red'>FAILURE: Key '$target' NOT found.</b><br>";
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
    echo "<hr>";
}

echo "<h1>Pruebas de Conexion PDO</h1>";

// Test 1: Standard with CASE_LOWER (Current state)
test_connection("CASE_LOWER + utf8mb4", [
    PDO::ATTR_CASE => PDO::CASE_LOWER,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Test 2: CASE_NATURAL
test_connection("CASE_NATURAL + utf8mb4", [
    PDO::ATTR_CASE => PDO::CASE_NATURAL,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Test 3: CASE_LOWER + SET NAMES
test_connection("CASE_LOWER + SET NAMES utf8mb4", [
    PDO::ATTR_CASE => PDO::CASE_LOWER,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
], ["SET NAMES 'utf8mb4'"]);

?>