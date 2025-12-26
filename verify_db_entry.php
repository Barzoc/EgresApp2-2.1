<?php
require_once 'modelo/Conexion.php';

$db = new Conexion();

echo "===========================================\n";
echo "VERIFICACIÓN DE DATOS EN BASE DE DATOS\n";
echo "===========================================\n\n";

// Verificar en queue
echo "[1] Verificando expediente_queue (ID: 2)...\n";
$stmt = $db->pdo->prepare('SELECT * FROM expediente_queue WHERE id = ?');
$stmt->execute([2]);
$queue = $stmt->fetch(PDO::FETCH_ASSOC);

if ($queue) {
    echo "✓ Encontrado en queue\n";
    echo "  - Status: {$queue['status']}\n";
    echo "  - Attempts: {$queue['attempts']}\n";
    echo "  - Last Error: " . ($queue['last_error'] ?? '(ninguno)') . "\n";
    echo "  - ID Expediente: " . ($queue['id_expediente'] ?? '(null)') . "\n";

    if (!empty($queue['result_payload'])) {
        echo "\n  PAYLOAD:\n";
        $payload = json_decode($queue['result_payload'], true);
        echo "  " . str_replace("\n", "\n  ", json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "\n";
    }
} else {
    echo "✗ No encontrado en queue\n";
}

echo "\n";

// Verificar tabla egresado
echo "[2] Verificando tabla egresado...\n";
$stmt = $db->pdo->query('SELECT COUNT(*) as total FROM egresado');
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  - Total de egresados: {$count['total']}\n";

// Verificar últimos 5 registros
$stmt = $db->pdo->query('SELECT identificacion, nombreCompleto, titulo, created_at FROM egresado ORDER BY created_at DESC LIMIT 5');
$ultimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($ultimos) {
    echo "\n  Últimos 5 egresados:\n";
    foreach ($ultimos as $eg) {
        echo sprintf(
            "    - %s | %s | %s\n",
            $eg['identificacion'] ?? 'N/A',
            substr($eg['nombreCompleto'] ?? 'N/A', 0, 30),
            $eg['created_at'] ?? 'N/A'
        );
    }
}

echo "\n";

// Buscar por RUT específico
echo "[3] Buscando RUT 16.466.056-7...\n";
$stmt = $db->pdo->prepare('SELECT * FROM egresado WHERE identificacion LIKE ?');
$stmt->execute(['%16.466.056%']);
$egresado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($egresado) {
    echo "✓ ENCONTRADO:\n";
    foreach ($egresado as $campo => $valor) {
        if (!in_array($campo, ['created_at', 'updated_at'])) {
            echo sprintf("  %-25s: %s\n", $campo, $valor ?? '(null)');
        }
    }
} else {
    echo "✗ No encontrado\n";
}

echo "\n===========================================\n";
