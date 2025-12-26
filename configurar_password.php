<?php
// Script CLI para establecer contraseña por defecto en la instalación
// Se ejecuta automáticamente por el instalador

$host = 'localhost';
$dbname = 'gestion_egresados';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Configurando contraseña de administrador...\n";

    // Contraseña por defecto: 12345678
    $newPassword = '12345678';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Detectar columna de contraseña (por si acaso)
    $stmt = $pdo->query("SHOW COLUMNS FROM usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $passwordColumn = 'password'; // Default

    foreach ($columns as $col) {
        $field = $col['Field'];
        if ($field !== 'id' && $field !== 'nombre' && $field !== 'email' && $field !== 'created_at') {
            $passwordColumn = $field;
            break;
        }
    }

    // Actualizar
    $sql = "UPDATE usuario SET `$passwordColumn` = :password WHERE email = 'admin@test.com'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['password' => $hashedPassword]);

    echo "[OK] Contraseña de admin@test.com establecida a: 12345678\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
?>