<?php
// Test de conexión directa a MySQL sin usar clases
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE CONEXIÓN DIRECTA A MYSQL ===\n\n";

$host = 'localhost';
$dbname = 'gestion_egresados';
$user = 'root';
$pass = '';

try {
    echo "1. Intentando conectar a MySQL...\n";
    echo "   Host: $host\n";
    echo "   Database: $dbname\n";
    echo "   User: $user\n\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ CONEXIÓN EXITOSA\n\n";
    
    // Test 1: Contar registros
    echo "2. Contando registros en tabla egresado...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM egresado");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total: " . $result['total'] . " registros\n\n";
    
    // Test 2: Obtener primeros 5 registros
    echo "3. Obteniendo primeros 5 registros...\n";
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, carnet, sexo FROM egresado LIMIT 5");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($registros as $i => $reg) {
        echo "   Registro " . ($i + 1) . ":\n";
        echo "     ID: " . $reg['identificacion'] . "\n";
        echo "     Nombre: " . $reg['nombreCompleto'] . "\n";
        echo "     Carnet: " . $reg['carnet'] . "\n";
        echo "     Sexo: " . $reg['sexo'] . "\n\n";
    }
    
    // Test 3: Verificar tablas
    echo "4. Verificando existencia de tablas...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'titulo'");
    $tituloExists = $stmt->fetch();
    echo "   Tabla 'titulo': " . ($tituloExists ? "✓ EXISTE" : "✗ NO EXISTE") . "\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tituloegresado'");
    $tituloEgresadoExists = $stmt->fetch();
    echo "   Tabla 'tituloegresado': " . ($tituloEgresadoExists ? "✓ EXISTE" : "✗ NO EXISTE") . "\n\n";
    
    // Test 4: Probar la consulta exacta que usa BuscarTodos (versión simple)
    echo "5. Probando consulta simple (sin JOIN)...\n";
    $sql = "SELECT e.identificacion, e.nombreCompleto, e.dirResidencia, e.telResidencia, e.telAlternativo, e.correoPrincipal,
            e.correoSecundario, e.carnet, e.sexo, e.fallecido, NULL as titulo_catalogo, e.tituloObtenido as titulo_obtenido, 
            e.numeroCertificado AS numerocertificado, e.avatar, e.expediente_pdf,
            e.expediente_drive_id, e.expediente_drive_link,
            NULL as fechaGrado, DATE_FORMAT(e.fechaEntregaCertificado, '%Y-%m-%d') as fechaEntregaCertificado
        FROM egresado e
        ORDER BY e.identificacion";
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Resultados: " . count($resultados) . " registros\n\n";
    
    if (count($resultados) > 0) {
        echo "   Primer registro:\n";
        echo "     ID: " . $resultados[0]['identificacion'] . "\n";
        echo "     Nombre: " . $resultados[0]['nombreCompleto'] . "\n";
        echo "     Carnet: " . $resultados[0]['carnet'] . "\n\n";
    }
    
    // Test 5: Convertir a JSON como lo hace el controlador
    echo "6. Convirtiendo a JSON...\n";
    $json = array();
    foreach ($resultados as $objeto) {
        $json[] = array(
            'identificacion' => $objeto['identificacion'],
            'nombreCompleto' => $objeto['nombreCompleto'],
            'dirResidencia' => $objeto['dirResidencia'],
            'telResidencia' => $objeto['telResidencia'],
            'telAlternativo' => $objeto['telAlternativo'],
            'correoPrincipal' => $objeto['correoPrincipal'],
            'correoSecundario' => $objeto['correoSecundario'],
            'carnet' => $objeto['carnet'],
            'sexo' => $objeto['sexo'],
            'fallecido' => $objeto['fallecido'],
            'titulo_catalogo' => $objeto['titulo_catalogo'],
            'titulo_obtenido' => $objeto['titulo_obtenido'],
            'titulo' => $objeto['titulo_obtenido'] ?? '',
            'numeroCertificado' => $objeto['numerocertificado'],
            'avatar' => $objeto['avatar'],
            'expediente_pdf' => $objeto['expediente_pdf'],
            'expediente_drive_id' => $objeto['expediente_drive_id'],
            'expediente_drive_link' => $objeto['expediente_drive_link'],
            'fechaGrado' => $objeto['fechaGrado'],
            'fechaEntregaCertificado' => $objeto['fechaEntregaCertificado']
        );
    }
    
    $jsonstring = json_encode($json);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "   ✓ JSON VÁLIDO\n";
        echo "   Longitud: " . strlen($jsonstring) . " caracteres\n";
        echo "   Registros en JSON: " . count($json) . "\n\n";
        
        echo "   Primeros 200 caracteres del JSON:\n";
        echo "   " . substr($jsonstring, 0, 200) . "...\n\n";
    } else {
        echo "   ✗ ERROR AL GENERAR JSON: " . json_last_error_msg() . "\n\n";
    }
    
    echo "=== TEST COMPLETADO EXITOSAMENTE ===\n";
    
} catch (PDOException $e) {
    echo "✗ ERROR DE CONEXIÓN:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
}
?>
