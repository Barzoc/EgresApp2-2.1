<?php
/**
 * Test de conexión MySQL a un host específico
 * Uso: php test_mysql_host.php [IP]
 */

$host = $argv[1] ?? null;

if (!$host) {
    echo "❌ Uso: php test_mysql_host.php [IP_DEL_SERVIDOR]\n";
    echo "Ejemplo: php test_mysql_host.php 192.168.1.100\n";
    exit(1);
}

echo "========================================\n";
echo "   TEST CONEXIÓN MYSQL\n";
echo "========================================\n\n";
echo "🔍 Probando servidor: $host\n";
echo "Puerto: 3306\n";
echo "Usuario: egresapp_remote\n\n";

// Leer configuración
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    $password = $config['central']['password'] ?? 'CAMBIAR_CONTRASEÑA';
} else {
    $password = 'CAMBIAR_CONTRASEÑA';
}

echo "🔄 Intentando conectar...\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=gestion_egresados;charset=utf8mb4",
        'egresapp_remote',
        $password,
        [
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    
    // Verificar conexión
    $pdo->query("SELECT 1");
    
    echo "\n========================================\n";
    echo "✅ ¡CONEXIÓN EXITOSA!\n";
    echo "========================================\n\n";
    echo "📍 Servidor encontrado en: $host\n";
    echo "🎯 Esta es la IP que debes usar en config/database.php\n\n";
    
    // Obtener información del servidor
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as db");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Información del servidor:\n";
    echo "   - MySQL Version: {$info['version']}\n";
    echo "   - Base de datos: {$info['db']}\n\n";
    
    exit(0);
    
} catch (PDOException $e) {
    echo "\n========================================\n";
    echo "❌ NO SE PUDO CONECTAR\n";
    echo "========================================\n\n";
    
    $error = $e->getMessage();
    
    if (strpos($error, 'SQLSTATE[HY000] [2002]') !== false) {
        echo "Error: No hay servidor MySQL en $host:3306\n";
        echo "Causas posibles:\n";
        echo "  • El servidor no está encendido\n";
        echo "  • El firewall está bloqueando el puerto 3306\n";
        echo "  • MySQL no está escuchando en esa IP\n";
    } elseif (strpos($error, 'Access denied') !== false) {
        echo "Error: Credenciales incorrectas\n";
        echo "El servidor existe pero el usuario/contraseña no es correcto.\n";
    } else {
        echo "Error: $error\n";
    }
    
    echo "\n";
    exit(1);
}
