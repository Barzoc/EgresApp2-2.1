<?php
/**
 * Script de Prueba de Conexión - Base de Datos Central
 * Verifica conectividad y funcionalidad básica
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/modelo/Conexion.php';

// Función para mostrar línea separadora
function separator($char = '=', $length = 60) {
    echo str_repeat($char, $length) . "\n";
}

// Función para mostrar mensaje con emoji
function message($emoji, $text) {
    echo "$emoji $text\n";
}

separator();
echo "   PRUEBA DE CONEXIÓN - BASE DE DATOS CENTRAL\n";
echo "   EGRESAPP2\n";
separator();
echo "\n";

try {
    // ========================================
    // PASO 1: Intentar Conexión
    // ========================================
    
    message("🔄", "Conectando a la base de datos...");
    $startTime = microtime(true);
    
    $conexion = new Conexion();
    
    $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
    
    if (!$conexion->pdo) {
        throw new Exception("No se pudo establecer conexión PDO");
    }
    
    message("✅", "Conexión establecida en {$connectionTime}ms\n");
    
    // ========================================
    // PASO 2: Información de Conexión
    // ========================================
    
    separator('-');
    message("📊", "INFORMACIÓN DE CONEXIÓN");
    separator('-');
    
    $info = $conexion->getConnectionInfo();
    
    echo sprintf("   Modo Activo      : %s\n", strtoupper($info['mode'] ?? 'desconocido'));
    echo sprintf("   Host             : %s\n", $info['host'] ?? 'N/A');
    echo sprintf("   Base de Datos    : %s\n", $info['database'] ?? 'N/A');
    echo sprintf("   Puerto           : %s\n", $info['port'] ?? 'N/A');
    echo sprintf("   Tipo Conexión    : %s\n", 
        $info['is_central'] ? '🌐 SERVIDOR CENTRAL' : '💻 BASE DE DATOS LOCAL'
    );
    
    if ($info['last_change']) {
        echo sprintf("   Último Cambio    : %s\n", $info['last_change']);
    }
    
    echo "\n";
    
    // Advertencia si está en modo local
    if (!$info['is_central']) {
        separator('-');
        message("⚠️", "ADVERTENCIA: Trabajando en modo LOCAL");
        separator('-');
        echo "   No estás conectado al servidor central.\n";
        echo "   Los cambios NO se sincronizarán con otros clientes.\n";
        echo "\n";
        echo "   Posibles causas:\n";
        echo "   • El servidor central no está accesible\n";
        echo "   • No hay conexión a Internet\n";
        echo "   • Credenciales incorrectas en config/database.php\n";
        echo "\n";
    }
    
    // ========================================
    // PASO 3: Verificar Estructura de BD
    // ========================================
    
    separator('-');
    message("🗄️", "VERIFICANDO ESTRUCTURA DE BASE DE DATOS");
    separator('-');
    
    $expectedTables = ['egresado', 'titulo', 'tituloegresado', 'configuracion_certificado'];
    $missingTables = [];
    
    foreach ($expectedTables as $table) {
        $stmt = $conexion->pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            message("✅", "Tabla '$table' existe");
        } else {
            message("❌", "Tabla '$table' NO encontrada");
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "\n";
        message("⚠️", "ADVERTENCIA: Faltan " . count($missingTables) . " tablas");
        echo "   Ejecuta el script de instalación de base de datos.\n";
    }
    
    echo "\n";
    
    // ========================================
    // PASO 4: Contar Registros
    // ========================================
    
    separator('-');
    message("📈", "ESTADÍSTICAS DE DATOS");
    separator('-');
    
    $totalRecords = 0;
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $missingTables)) {
            continue;
        }
        
        try {
            $stmt = $conexion->pdo->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)($result['total'] ?? 0);
            $totalRecords += $count;
            
            echo sprintf("   %-30s : %d registros\n", ucfirst($table), $count);
        } catch (Exception $e) {
            echo sprintf("   %-30s : ⚠️  Error al contar\n", ucfirst($table));
        }
    }
    
    echo sprintf("\n   %-30s : %d registros\n", "TOTAL", $totalRecords);
    echo "\n";
    
    // ========================================
    // PASO 5: Test de Salud
    // ========================================
    
    separator('-');
    message("💚", "TEST DE SALUD DE CONEXIÓN");
    separator('-');
    
    $health = $conexion->getHealthStatus();
    
    if ($health['healthy']) {
        message("✅", $health['message']);
        echo sprintf("   Latencia         : %sms\n", $health['latency_ms']);
        
        // Evaluación de latencia
        if ($health['latency_ms'] < 50) {
            message("🚀", "Latencia EXCELENTE (muy rápida)");
        } elseif ($health['latency_ms'] < 150) {
            message("✅", "Latencia BUENA (normal)");
        } elseif ($health['latency_ms'] < 500) {
            message("⚠️", "Latencia MODERADA (puede ser lenta)");
        } else {
            message("❌", "Latencia ALTA (revisar conexión)");
        }
    } else {
        message("❌", $health['message']);
    }
    
    echo "\n";
    
    // ========================================
    // PASO 6: Test de Escritura
    // ========================================
    
    separator('-');
    message("✏️", "TEST DE OPERACIONES DE ESCRITURA");
    separator('-');
    
    try {
        $conexion->pdo->beginTransaction();
        
        // Crear tabla temporal
        $conexion->pdo->exec("
            CREATE TEMPORARY TABLE test_connection (
                id INT AUTO_INCREMENT PRIMARY KEY,
                test_data VARCHAR(100),
                client_name VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insertar datos de prueba
        $hostname = gethostname();
        $stmt = $conexion->pdo->prepare("INSERT INTO test_connection (test_data, client_name) VALUES (?, ?)");
        $stmt->execute(['Test de conexión exitoso', $hostname]);
        
        // Leer datos
        $stmt = $conexion->pdo->query("SELECT * FROM test_connection");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $conexion->pdo->rollBack(); // No guardar cambios
        
        message("✅", "Operaciones de escritura funcionan correctamente");
        echo sprintf("   Cliente          : %s\n", $result['client_name'] ?? 'N/A');
        echo sprintf("   Timestamp        : %s\n", $result['created_at'] ?? 'N/A');
    } catch (Exception $e) {
        $conexion->pdo->rollBack();
        message("❌", "Error en operación de escritura: " . $e->getMessage());
    }
    
    echo "\n";
    
    // ========================================
    // RESUMEN FINAL
    // ========================================
    
    separator();
    
    if ($info['is_central'] && $health['healthy']) {
        message("✅", "PRUEBA COMPLETADA EXITOSAMENTE");
        separator();
        echo "\n";
        echo "   🌐 Conectado al SERVIDOR CENTRAL\n";
        echo "   ✅ Todas las funciones operativas\n";
        echo "   ✅ Listo para usar EGRESAPP2\n";
        echo "\n";
    } elseif (!$info['is_central'] && $health['healthy']) {
        message("⚠️", "MODO LOCAL ACTIVO");
        separator();
        echo "\n";
        echo "   💻 Usando base de datos LOCAL\n";
        echo "   ⚠️  No hay sincronización con el servidor central\n";
        echo "   ✅ Puedes trabajar offline\n";
        echo "\n";
        echo "   Para conectar al servidor central:\n";
        echo "   1. Verifica config/database.php\n";
        echo "   2. Asegúrate de tener conexión a Internet\n";
        echo "   3. Verifica que el servidor central esté activo\n";
        echo "\n";
    } else {
        message("❌", "HAY PROBLEMAS CON LA CONEXIÓN");
        separator();
        echo "\n";
        echo "   Revisa el archivo de logs para más detalles:\n";
        echo "   logs/database.log\n";
        echo "\n";
    }
    
    // ========================================
    // Guardar Reporte
    // ========================================
    
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'hostname' => gethostname(),
        'connection_info' => $info,
        'health' => $health,
        'table_counts' => [],
        'total_records' => $totalRecords,
        'status' => $info['is_central'] && $health['healthy'] ? 'optimal' : 
                   (!$info['is_central'] && $health['healthy'] ? 'local_mode' : 'error'),
    ];
    
    $reportFile = __DIR__ . '/logs/connection_test_' . date('Ymd_His') . '.json';
    $logDir = dirname($reportFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "📄 Reporte guardado en: $reportFile\n\n";
    
    separator();
    
    // Exit code: 0 = éxito, 1 = modo local, 2 = error
    exit($info['is_central'] ? 0 : 1);
    
} catch (Exception $e) {
    echo "\n";
    separator();
    message("❌", "ERROR DE CONEXIÓN");
    separator();
    echo "\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "\n";
    echo "   Posibles causas:\n";
    echo "   • El servidor central no está accesible\n";
    echo "   • Firewall o router bloqueando puerto 3306\n";
    echo "   • Credenciales incorrectas en config/database.php\n";
    echo "   • MySQL no configurado para acceso remoto\n";
    echo "   • No hay base de datos local instalada\n";
    echo "\n";
    echo "   Soluciones:\n";
    echo "   1. Verificar config/database.php\n";
    echo "   2. Ejecutar CONFIGURAR_SERVIDOR_CENTRAL.bat en servidor\n";
    echo "   3. Revisar logs en logs/database.log\n";
    echo "   4. Verificar conexión a Internet\n";
    echo "\n";
    
    separator();
    
    exit(2);
}
