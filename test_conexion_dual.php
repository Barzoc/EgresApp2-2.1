<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión Dual</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .status {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .log-container {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
        .log-line {
            margin: 3px 0;
            line-height: 1.5;
        }
        .log-success { color: #4ec9b0; }
        .log-error { color: #f48771; }
        .log-warning { color: #dcdcaa; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔄 Test de Conexión Dual con Auto-Sincronización</h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        require_once __DIR__ . '/modelo/Conexion.php';
        
        try {
            echo "<h2>📡 Iniciando prueba de conexión...</h2>";
            
            // Instanciar la conexión (esto dispara la auto-sincronización)
            $db = new Conexion();
            
            // Verificar que la conexión local funcione
            if ($db->pdo) {
                echo "<div class='status success'>";
                echo "✅ Conexión a base de datos LOCAL exitosa<br>";
                echo "<strong>Modo de operación:</strong> " . $db->getModoConexion();
                if ($db->getUltimaSincronizacion()) {
                    echo "<br><strong>Última sincronización:</strong> " . $db->getUltimaSincronizacion();
                }
                echo "</div>";
                
                // Verificar datos en la tabla egresado
                $sql = "SELECT COUNT(*) as total FROM egresado";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>📊 Datos en Base Local</h3>";
                echo "<table>";
                echo "<tr><th>Tabla</th><th>Registros</th></tr>";
                echo "<tr><td>egresado</td><td><span class='badge badge-success'>{$result['total']}</span></td></tr>";
                
                // Contar títulos
                $sql = "SELECT COUNT(*) as total FROM titulo";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<tr><td>titulo</td><td><span class='badge badge-success'>{$result['total']}</span></td></tr>";
                echo "</table>";
                
                // Mostrar últimos 5 egresados
                echo "<h3>👥 Últimos 5 Egresados</h3>";
                $sql = "SELECT identificacion, nombreCompleto, tituloObtenido, fechaEntregaCertificado FROM egresado ORDER BY identificacion DESC LIMIT 5";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $egresados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Título</th><th>Fecha Entrega</th></tr>";
                foreach ($egresados as $eg) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($eg['identificacion']) . "</td>";
                    echo "<td>" . htmlspecialchars($eg['nombrecompleto'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($eg['tituloobtenido'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($eg['fechaentregacertificado'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
            } else {
                echo "<div class='status error'>";
                echo "❌ Error: No se pudo establecer conexión a la base de datos";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='status error'>";
            echo "❌ Error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>
        
        <h3>📝 Log de Sincronización</h3>
        <div class="log-container">
            <?php
            $log_file = __DIR__ . '/logs/sincronizacion.log';
            if (file_exists($log_file)) {
                $logs = file($log_file);
                $last_logs = array_slice($logs, -30); // Últimas 30 líneas
                
                foreach ($last_logs as $line) {
                    $class = 'log-line';
                    if (strpos($line, '✓') !== false) {
                        $class .= ' log-success';
                    } elseif (strpos($line, '✗') !== false) {
                        $class .= ' log-error';
                    } elseif (strpos($line, '⚠') !== false) {
                        $class .= ' log-warning';
                    }
                    
                    echo "<div class='$class'>" . htmlspecialchars($line) . "</div>";
                }
            } else {
                echo "<div class='log-line log-warning'>⚠ No hay logs de sincronización disponibles</div>";
            }
            ?>
        </div>
    </div>
    
    <div class="card">
        <h2>ℹ️ Información del Sistema</h2>
        <table>
            <tr>
                <th>Configuración</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Servidor Central</td>
                <td>26.234.93.144:3306</td>
            </tr>
            <tr>
                <td>Base de Datos</td>
                <td>gestion_egresados</td>
            </tr>
            <tr>
                <td>Modo de Trabajo</td>
                <td>Siempre LOCAL (con sincronización automática al inicio)</td>
            </tr>
            <tr>
                <td>Timeout Conexión Central</td>
                <td>3 segundos</td>
            </tr>
        </table>
    </div>
</body>
</html>
