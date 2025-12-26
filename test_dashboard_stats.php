<?php
include_once 'modelo/Egresado.php';

$egresado = new Egresado();

echo "--- Testing obtenerDatosTitulo ---\n";
$titulos = $egresado->obtenerDatosTitulo();
print_r($titulos);

echo "\n--- Testing obtenerDatosGraduacion ---\n";
$graduacion = $egresado->obtenerDatosGraduacion();
print_r($graduacion);

echo "\n--- Testing obtenerDatosMes ---\n";
if (method_exists($egresado, 'obtenerDatosMes')) {
    $meses = $egresado->obtenerDatosMes();
    print_r($meses);
} else {
    echo "Method obtenerDatosMes does not exist.\n";
}
?>
