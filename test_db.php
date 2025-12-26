<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'modelo/Conexion.php';
include_once 'modelo/AgregarTitulo.php';

echo "Testing database connection...\n";

try {
    $db = new Conexion();
    echo "✓ Database connection successful\n\n";
    
    echo "Testing AgregarTitulo->BuscarTodos()...\n";
    $titulo = new AgregarTitulo();
    $titulo->BuscarTodos('');
    
    echo "✓ Query executed successfully\n";
    echo "Results: " . count($titulo->objetos) . " records found\n\n";
    
    echo "JSON output:\n";
    $json = Array();
    foreach ($titulo->objetos as $objeto) {
        $json[]=array(
            'id'=>$objeto->id,
            'nombre'=>$objeto->nombre
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}
?>
