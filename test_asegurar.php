<?php
// Test para verificar si asegurarColumnasCertificado está causando problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE asegurarColumnasCertificado ===\n\n";

include_once 'modelo/Conexion.php';
include_once 'modelo/Egresado.php';

try {
    $egresado = new Egresado();
    
    echo "1. Intentando ejecutar BuscarTodos sin asegurarColumnasCertificado...\n";
    
    // Comentar temporalmente la llamada a asegurarColumnasCertificado
    // en el método BuscarTodos para ver si eso es el problema
    
    $egresado->BuscarTodos('');
    
    echo "2. Resultados: " . count($egresado->objetos) . " registros\n\n";
    
    if (count($egresado->objetos) > 0) {
        echo "3. Primer registro:\n";
        print_r($egresado->objetos[0]);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}

echo "\n\n=== FIN DEL TEST ===\n";
?>
