<?php
// Test directo de la consulta de egresados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE CONSULTA EGRESADOS ===\n\n";

include_once 'modelo/Conexion.php';
include_once 'modelo/Egresado.php';

try {
    $db = new Conexion();
    echo "1. Conexión establecida\n";
    
    $egresado = new Egresado();
    echo "2. Modelo Egresado creado\n";
    
    echo "3. Ejecutando BuscarTodos('')...\n";
    $egresado->BuscarTodos('');
    
    echo "4. Resultados encontrados: " . count($egresado->objetos) . "\n\n";
    
    if (count($egresado->objetos) > 0) {
        echo "5. Primeros 3 registros:\n";
        for ($i = 0; $i < min(3, count($egresado->objetos)); $i++) {
            $obj = $egresado->objetos[$i];
            echo "   - ID: " . ($obj->identificacion ?? 'NULL') . "\n";
            echo "     Nombre: " . ($obj->nombrecompleto ?? 'NULL') . "\n\n";
        }
    } else {
        echo "5. No se encontraron registros\n";
        echo "   Verificando si hay datos en la tabla...\n";
        
        $sql = "SELECT COUNT(*) as total FROM egresado";
        $query = $db->pdo->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        echo "   Total de registros en la tabla: " . $result['total'] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}

echo "\n\n=== FIN DEL TEST ===\n";
?>
