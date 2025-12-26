<?php
// diagnostico_login_v3.php
require_once 'modelo/Conexion.php';

echo "<h1>Diagnostico de Login V3 (Post-Fix)</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    echo "<h2>Usuario admin@test.com</h2>";
    $sql = "SELECT * FROM usuario WHERE email = 'admin@test.com'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Usuario encontrado.<br>";

        $target = 'password';
        if (isset($user[$target])) {
            echo "<b style='color:green'>EXITO: Columna '$target' encontrada.</b><br>";
            echo "Hash: " . substr($user[$target], 0, 10) . "...<br>";

            if (password_verify('12345678', $user[$target])) {
                echo "<b style='color:green'>VERIFICACION DE PASSWORD: OK</b><br>";
            } else {
                echo "<b style='color:red'>VERIFICACION DE PASSWORD: FALLO</b><br>";
            }
        } else {
            echo "<b style='color:red'>FALLO: Columna '$target' NO encontrada.</b><br>";
            echo "Keys disponibles: " . implode(", ", array_keys($user));
        }

    } else {
        echo "Usuario no encontrado.";
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage();
}
?>