<?php
require_once __DIR__ . '/modelo/Egresado.php';

$egresado = new Egresado();
$id = '1';

echo "=== Probando función Eliminar ===\n\n";

// Capturar la salida
ob_start();
$egresado->Eliminar($id);
$output = ob_get_clean();

echo "Resultado: $output\n";

if ($output === 'eliminado') {
    echo "✓ La función devolvió 'eliminado' (pero se hizo rollback en la función)\n";
} else {
    echo "✗ La función devolvió 'noeliminado'\n";
}
