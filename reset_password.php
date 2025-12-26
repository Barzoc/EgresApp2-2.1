<?php
// Script para resetear password del administrador - Version Mejorada con Deteccion de Columnas
// Ejecutar desde: http://localhost/EGRESAPP2/reset_password.php

// Configuración de base de datos
$host = 'localhost';
$dbname = 'gestion_egresados';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Reset Password - EGRESAPP2 (Diagnóstico)</h2>";

    // 1. Detectar nombre de la columna de contraseña
    echo "<h3>1. Analizando estructura de tabla 'usuario'...</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $passwordColumn = null;

    echo "<ul>";
    foreach ($columns as $col) {
        $field = $col['Field'];
        echo "<li>Columna encontrada: <strong>" . htmlspecialchars($field) . "</strong></li>";

        // Buscar columna que parezca contraseña (no id, nombre, email, created_at)
        if ($field !== 'id' && $field !== 'nombre' && $field !== 'email' && $field !== 'created_at') {
            $passwordColumn = $field;
        }
    }
    echo "</ul>";

    if ($passwordColumn) {
        echo "<p style='color: blue;'>Columna de contraseña detectada: <strong>" . htmlspecialchars($passwordColumn) . "</strong></p>";

        // 2. Actualizar password
        echo "<h3>2. Actualizando password...</h3>";

        // Nueva contraseña
        $newPassword = '12345678';
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Usamos el nombre de columna detectado dinámicamente
        $sql = "UPDATE usuario SET `$passwordColumn` = :password WHERE email = 'admin@test.com'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $hashedPassword]);

        echo "<p style='color: green;'>✓ Password reseteada exitosamente en columna '$passwordColumn'</p>";
        echo "<p><strong>Email:</strong> admin@test.com</p>";
        echo "<p><strong>Password:</strong> 12345678</p>";
        echo "<p><a href='index.php'>Ir al login</a></p>";

        // 3. Verificar
        $stmt = $pdo->prepare("SELECT email, nombre FROM usuario WHERE email = 'admin@test.com'");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "<p>Usuario verificado: " . htmlspecialchars($user['nombre']) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>ERROR: No se pudo identificar la columna de contraseña.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>