<?php
// Test directo del controlador
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DIRECTO DEL CONTROLADOR ===\n\n";

// Simular POST
$_POST['funcion'] = 'listar';

echo "1. Incluyendo modelo...\n";
include_once 'modelo/AgregarTitulo.php';

echo "2. Creando instancia...\n";
$titulo = new AgregarTitulo();

echo "3. Ejecutando BuscarTodos...\n";
$titulo->BuscarTodos('');

echo "4. Resultados encontrados: " . count($titulo->objetos) . "\n\n";

echo "5. Generando JSON...\n";
$json = Array();
foreach ($titulo->objetos as $objeto) {
    $json[]=array(
        'id'=>$objeto->id,
        'nombre'=>$objeto->nombre
    );
}

$jsonstring = json_encode($json);

echo "6. JSON generado:\n";
echo $jsonstring;

echo "\n\n7. Validación JSON:\n";
$decoded = json_decode($jsonstring);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✓ JSON válido\n";
} else {
    echo "✗ JSON inválido: " . json_last_error_msg() . "\n";
}

echo "\n\n=== FIN DEL TEST ===\n";
?>
