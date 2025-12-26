<?php
// Test DIRECTO del endpoint que usa DataTables
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DIRECTO DEL ENDPOINT ===\n\n";

// Simular la petición POST que hace DataTables
$_POST['funcion'] = 'listar';

echo "Ejecutando EgresadoController.php con funcion=listar...\n\n";

// Capturar la salida
ob_start();
include 'controlador/EgresadoController.php';
$output = ob_get_clean();

echo "SALIDA DEL CONTROLADOR:\n";
echo "======================\n";
echo $output;
echo "\n======================\n\n";

// Validar si es JSON válido
$decoded = json_decode($output);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✓ JSON VÁLIDO\n";
    echo "Cantidad de registros: " . count($decoded) . "\n\n";
    
    if (count($decoded) > 0) {
        echo "Primer registro:\n";
        print_r($decoded[0]);
    }
} else {
    echo "✗ JSON INVÁLIDO\n";
    echo "Error: " . json_last_error_msg() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
