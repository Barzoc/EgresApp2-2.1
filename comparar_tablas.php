<?php
require_once __DIR__ . '/modelo/Conexion.php';

$conexion = new Conexion();
$db = $conexion->pdo;

echo "=== Comparación de datos: egresado vs tituloegresado ===\n\n";

// Columnas de egresado
$sql = "DESCRIBE egresado";
$query = $db->query($sql);
$egresadoCols = $query->fetchAll(PDO::FETCH_COLUMN);

echo "Columnas en 'egresado':\n";
foreach ($egresadoCols as $col) {
    echo "  - $col\n";
}

// Columnas de tituloegresado
$sql = "DESCRIBE tituloegresado";
$query = $db->query($sql);
$tituloCols = $query->fetchAll(PDO::FETCH_COLUMN);

echo "\nColumnas en 'tituloegresado':\n";
foreach ($tituloCols as $col) {
    echo "  - $col\n";
}

// Comparar datos de un egresado
echo "\n=== Comparación de datos para ID 1 (ADRIAN) ===\n\n";

$sql = "SELECT * FROM egresado WHERE identificacion = 1";
$query = $db->query($sql);
$egresado = $query->fetch(PDO::FETCH_ASSOC);

echo "Datos en 'egresado':\n";
echo "  - tituloObtenido: {$egresado['tituloobtenido']}\n";
echo "  - fechaEntregaCertificado: {$egresado['fechaentregacertificado']}\n";
echo "  - numeroCertificado: {$egresado['numerocertificado']}\n";
echo "  - anioEgreso: {$egresado['anioegreso']}\n";

$sql = "SELECT te.*, t.nombre as titulo_nombre 
        FROM tituloegresado te 
        LEFT JOIN titulo t ON te.id = t.id 
        WHERE te.identificacion = 1";
$query = $db->query($sql);
$titulo = $query->fetch(PDO::FETCH_ASSOC);

if ($titulo) {
    echo "\nDatos en 'tituloegresado':\n";
    echo "  - titulo (desde catálogo): {$titulo['titulo_nombre']}\n";
    echo "  - fechaGrado: {$titulo['fechagrado']}\n";
    echo "  - numero_documento: " . ($titulo['numero_documento'] ?? 'NULL') . "\n";
} else {
    echo "\nNo hay registro en 'tituloegresado' para este egresado.\n";
}

echo "\n=== Análisis ===\n";
echo "Datos DUPLICADOS:\n";
echo "  - Título está en ambas tablas\n";
echo "  - Fecha (fechaEntregaCertificado vs fechaGrado)\n\n";

echo "Datos ÚNICOS en tituloegresado:\n";
echo "  - Relación con catálogo de títulos (tabla 'titulo')\n";
echo "  - fechaGrado (puede ser diferente a fechaEntregaCertificado)\n\n";

echo "=== Conclusión ===\n";
echo "Si TODOS los datos necesarios están en 'egresado', entonces:\n";
echo "  ✓ tituloegresado se puede ELIMINAR\n";
echo "  ✓ Simplificaría la base de datos\n";
echo "  ✓ Evitaría duplicación de datos\n\n";

echo "Pero primero hay que:\n";
echo "  1. Eliminar los LEFT JOIN en el código\n";
echo "  2. Usar solo egresado.tituloObtenido en vez de titulo.nombre\n";
echo "  3. Eliminar referencias a tituloegresado en queries\n";
