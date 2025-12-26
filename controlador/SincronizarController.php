<?php
/**
 * SincronizarController.php
 * Controlador para sincronización manual con servidor central
 */

require_once __DIR__ . '/../modelo/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    session_start();
} catch (Throwable $e) {
    // Ignorar si ya está iniciada
}

// Verificar si hay sesión activa
if (!isset($_SESSION['s_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'mensaje' => 'No autorizado. Por favor inicia sesión.'
    ]);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'sincronizar') {
    try {
        // Crear nueva conexión que forzará la sincronización
        $db = new Conexion();
        
        $modo = $db->getModoConexion();
        $ultimaSync = $db->getUltimaSincronizacion();
        
        if ($modo === 'SINCRONIZADO') {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Sincronización completada exitosamente',
                'modo' => $modo,
                'ultima_sincronizacion' => $ultimaSync,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se pudo conectar al servidor central. Trabajando en modo local.',
                'modo' => $modo,
                'ultima_sincronizacion' => $ultimaSync
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error durante la sincronización: ' . $e->getMessage()
        ]);
    }
    
} elseif ($accion === 'estado') {
    try {
        $db = new Conexion();
        
        echo json_encode([
            'success' => true,
            'modo' => $db->getModoConexion(),
            'ultima_sincronizacion' => $db->getUltimaSincronizacion()
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener estado: ' . $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Acción no especificada'
    ]);
}
