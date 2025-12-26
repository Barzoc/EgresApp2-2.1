<?php
// Test para verificar si las tablas titulo y tituloegresado existen y tienen datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICACIÓN DE TABLAS ===\n\n";

include_once 'modelo/Conexion.php';

try {
    $db = new Conexion();
    
    // Verificar tabla titulo
    echo "1. Verificando tabla 'titulo'...\n";
    try {
        $sql = "SELECT COUNT(*) as total FROM titulo";
        $query = $db->pdo->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        echo "   ✓ Tabla 'titulo' existe\n";
        echo "   Registros: " . $result['total'] . "\n\n";
    } catch (Exception $e) {
        echo "   ✗ Error con tabla 'titulo': " . $e->getMessage() . "\n\n";
    }
    
    // Verificar tabla tituloegresado
    echo "2. Verificando tabla 'tituloegresado'...\n";
    try {
        $sql = "SELECT COUNT(*) as total FROM tituloegresado";
        $query = $db->pdo->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        echo "   ✓ Tabla 'tituloegresado' existe\n";
        echo "   Registros: " . $result['total'] . "\n\n";
    } catch (Exception $e) {
        echo "   ✗ Error con tabla 'tituloegresado': " . $e->getMessage() . "\n\n";
    }
    
    // Probar la consulta con LEFT JOIN
    echo "3. Probando consulta con LEFT JOIN...\n";
    try {
        $sql = "SELECT e.identificacion, e.nombreCompleto, e.carnet, 
                       t.nombre as titulo_catalogo, e.tituloObtenido as titulo_obtenido
                FROM egresado e
                LEFT JOIN tituloegresado te ON e.identificacion = te.identificacion
                LEFT JOIN titulo t ON te.id = t.id
                LIMIT 5";
        $query = $db->pdo->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        echo "   ✓ Consulta ejecutada\n";
        echo "   Resultados: " . count($results) . "\n";
        if (count($results) > 0) {
            echo "   Primer registro:\n";
            print_r($results[0]);
        }
        echo "\n";
    } catch (Exception $e) {
        echo "   ✗ Error en consulta: " . $e->getMessage() . "\n\n";
    }
    
    // Probar consulta simple sin JOIN
    echo "4. Probando consulta simple (sin JOIN)...\n";
    try {
        $sql = "SELECT identificacion, nombreCompleto, carnet, sexo, tituloObtenido
                FROM egresado
                LIMIT 5";
        $query = $db->pdo->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        echo "   ✓ Consulta ejecutada\n";
        echo "   Resultados: " . count($results) . "\n";
        if (count($results) > 0) {
            echo "   Primer registro:\n";
            print_r($results[0]);
        }
    } catch (Exception $e) {
        echo "   ✗ Error en consulta: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR GENERAL: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>
