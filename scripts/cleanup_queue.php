<?php
require_once __DIR__ . '/../modelo/ExpedienteQueue.php';

try {
    $queue = new ExpedienteQueue();
    echo "Limpiando tabla expediente_queue...\n";
    $queue->clearAll();
    echo "✅ Tabla limpiada exitosamente.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
