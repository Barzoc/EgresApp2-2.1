<?php
// check_config_table.php
require_once 'modelo/Conexion.php';

echo "<h1>Verificación de Configuración de Certificado</h1>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Check if table exists
    echo "<h2>1. ¿Existe la tabla configuracion_certificado?</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion_certificado'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<b style='color:green'>SÍ, la tabla existe.</b><br><br>";

        // 2. Show current data
        echo "<h2>2. Datos actuales en la tabla</h2>";
        $stmt = $pdo->query("SELECT * FROM configuracion_certificado");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($rows);
        echo "</pre>";

        // 3. Try to insert test data
        echo "<h2>3. Intentando guardar datos de prueba</h2>";
        $testNombre = "CAROLINA HIDALGO SOFJER";
        $testCargo = "RECTORA";

        // Delete if exists
        $pdo->exec("DELETE FROM configuracion_certificado WHERE clave IN ('firmante_nombre', 'firmante_cargo')");

        // Insert
        $stmt = $pdo->prepare("INSERT INTO configuracion_certificado (clave, valor) VALUES (?, ?)");
        $stmt->execute(['firmante_nombre', $testNombre]);
        $stmt->execute(['firmante_cargo', $testCargo]);

        echo "<b style='color:green'>Datos de prueba insertados.</b><br><br>";

        // 4. Verify
        echo "<h2>4. Verificación después de insertar</h2>";
        $stmt = $pdo->query("SELECT * FROM configuracion_certificado WHERE clave LIKE 'firmante%'");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($rows);
        echo "</pre>";

    } else {
        echo "<b style='color:red'>NO, la tabla NO existe.</b><br>";
        echo "Creando tabla...<br>";

        $sql = "CREATE TABLE configuracion_certificado (
            clave VARCHAR(100) PRIMARY KEY,
            valor TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $pdo->exec($sql);
        echo "<b style='color:green'>Tabla creada exitosamente.</b><br>";
    }

} catch (Exception $e) {
    echo "<b style='color:red'>Error: " . $e->getMessage() . "</b>";
}
?>