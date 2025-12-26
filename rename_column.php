<?php
// rename_column.php
require_once 'modelo/Conexion.php';

echo "<h1>Renombrando Columna</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // Check if column exists first
    $stmt = $pdo->query("SHOW COLUMNS FROM usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasContrasena = false;
    $hasPassword = false;

    echo "<pre>";
    foreach ($columns as $col) {
        // Normalize keys to lowercase to handle PDO::CASE_LOWER or CASE_NATURAL
        $col = array_change_key_case($col, CASE_LOWER);
        $field = strtolower($col['field']);
        echo "Found column: $field\n";

        if (strpos($field, 'contra') !== false)
            $hasContrasena = true;
        if ($field === 'password')
            $hasPassword = true;
    }
    echo "</pre>";

    if ($hasPassword) {
        echo "La columna 'password' ya existe. No es necesario renombrar.<br>";
    } elseif ($hasContrasena) {
        // Rename
        // Note: We need to know the definition to CHANGE it. 
        // Based on SQL: `contraseña` varchar(255) NOT NULL
        $sql = "ALTER TABLE `usuario` CHANGE `contraseña` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL";
        $pdo->exec($sql);
        echo "<b style='color:green'>EXITO: Columna 'contraseña' renombrada a 'password'.</b><br>";
    } else {
        echo "<b style='color:red'>ERROR: No se encontró la columna 'contraseña' para renombrar.</b><br>";
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage();
}
?>