<?php
// recreate_table.php
require_once 'modelo/Conexion.php';

echo "<h1>Recreando Tabla Usuario</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Drop Table
    $pdo->exec("DROP TABLE IF EXISTS `usuario`");
    echo "Tabla 'usuario' eliminada.<br>";

    // 2. Create Table (with 'password' column)
    $sql = "CREATE TABLE `usuario` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nombre` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $pdo->exec($sql);
    echo "Tabla 'usuario' creada correctamente.<br>";

    // 3. Seed Admin User
    // Password: 12345678
    $pass = '12345678';
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    $insert = "INSERT INTO `usuario` (`nombre`, `email`, `password`) VALUES ('admin', 'admin@test.com', :hash)";
    $stmt = $pdo->prepare($insert);
    $stmt->execute([':hash' => $hash]);

    echo "Usuario admin@test.com insertado con password hash.<br>";
    echo "<b style='color:green'>EXITO: Tabla restaurada y usuario creado.</b>";

} catch (Exception $e) {
    echo "<b style='color:red'>ERROR: " . $e->getMessage() . "</b>";
}
?>