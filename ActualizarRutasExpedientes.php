<?php
/**
 * ActualizarRutasExpedientes.php
 * 
 * Actualiza las rutas de expedientes_pdf en la base de datos
 * para que coincidan con los archivos reales después de la limpieza
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/modelo/Conexion.php';

echo "=======================================================\n";
echo "  ACTUALIZACIÓN DE RUTAS EN BASE DE DATOS\n";
echo "=======================================================\n\n";

$db = new Conexion();
$pdo = $db->pdo;

// Directorio base de expedientes
$baseDir = __DIR__ . '/assets/expedientes';

echo "[1/4] Obteniendo registros de la base de datos...\n";

// Obtener todos los registros con expedientes
$sql = "SELECT identificacion, nombreCompleto, expediente_pdf, tituloObtenido 
        FROM egresado 
        WHERE expediente_pdf IS NOT NULL AND expediente_pdf != ''
        ORDER BY identificacion";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "  Total de registros con expediente: " . count($registros) . "\n\n";

echo "[2/4] Verificando archivos y rutas...\n\n";

$actualizados = 0;
$noEncontrados = 0;
$correctos = 0;
$errores = [];

foreach ($registros as $reg) {
    $id = $reg['identificacion'];
    $rutaActual = $reg['expediente_pdf'];
    $nombre = $reg['nombrecompleto'] ?? $reg['nombreCompleto'];
    
    // Normalizar ruta actual
    $rutaActual = str_replace('\\', '/', $rutaActual);
    $archivoActual = $baseDir . '/' . ltrim($rutaActual, '/');
    
    echo "ID: $id | $nombre\n";
    echo "  Ruta BD: $rutaActual\n";
    
    // Verificar si el archivo existe en la ruta actual
    if (file_exists($archivoActual)) {
        echo "  [OK] Archivo existe en la ruta correcta\n";
        $correctos++;
    } else {
        echo "  [!!] Archivo NO existe en: $archivoActual\n";
        
        // Buscar el archivo en todo el directorio de expedientes
        $nombreArchivo = basename($rutaActual);
        $encontrado = buscarArchivo($baseDir, $nombreArchivo);
        
        if ($encontrado) {
            // Calcular ruta relativa
            $rutaNueva = str_replace('\\', '/', substr($encontrado, strlen($baseDir) + 1));
            
            echo "  [->] Encontrado en: $rutaNueva\n";
            
            // Actualizar en la base de datos
            try {
                $sqlUpdate = "UPDATE egresado SET expediente_pdf = :ruta WHERE identificacion = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':ruta' => $rutaNueva,
                    ':id' => $id
                ]);
                
                echo "  [OK] Base de datos actualizada\n";
                $actualizados++;
            } catch (Exception $e) {
                echo "  [X] Error actualizando: " . $e->getMessage() . "\n";
                $errores[] = "ID $id: " . $e->getMessage();
            }
        } else {
            echo "  [X] Archivo no encontrado en ninguna parte\n";
            $noEncontrados++;
            $errores[] = "ID $id: Archivo '$nombreArchivo' no encontrado";
        }
    }
    
    echo "\n";
}

echo "[3/4] Buscando archivos huérfanos (en disco pero no en BD)...\n\n";

$archivosEnDisco = encontrarTodosPDFs($baseDir);
$archivosEnBD = array_map(function($r) {
    return basename($r['expediente_pdf']);
}, $registros);

$huerfanos = 0;
foreach ($archivosEnDisco as $archivo) {
    $nombreArchivo = basename($archivo);
    if (!in_array($nombreArchivo, $archivosEnBD)) {
        echo "  [?] Huérfano: $nombreArchivo\n";
        $huerfanos++;
    }
}

if ($huerfanos === 0) {
    echo "  [OK] No hay archivos huérfanos\n";
}

echo "\n[4/4] Sincronizando con servidor central...\n";

// Intentar sincronizar
try {
    $db2 = new Conexion();
    if ($db2->getModoConexion() === 'SINCRONIZADO') {
        echo "  [OK] Sistema sincronizado con servidor central\n";
        echo "  Última sincronización: " . $db2->getUltimaSincronizacion() . "\n";
    } else {
        echo "  [!!] Trabajando solo con BD local\n";
    }
} catch (Exception $e) {
    echo "  [!] No se pudo verificar sincronización\n";
}

echo "\n=======================================================\n";
echo "                 RESUMEN\n";
echo "=======================================================\n\n";
echo "Registros totales:      " . count($registros) . "\n";
echo "Rutas correctas:        $correctos\n";
echo "Rutas actualizadas:     $actualizados\n";
echo "Archivos no encontrados: $noEncontrados\n";
echo "Archivos huérfanos:     $huerfanos\n";
echo "Errores:                " . count($errores) . "\n\n";

if (count($errores) > 0) {
    echo "Detalle de errores:\n";
    foreach ($errores as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

if ($actualizados > 0 || count($errores) > 0) {
    echo "Estado: COMPLETADO CON CAMBIOS\n";
} elseif ($correctos === count($registros)) {
    echo "Estado: TODO CORRECTO - NO SE REQUIRIERON CAMBIOS\n";
} else {
    echo "Estado: REVISIÓN NECESARIA\n";
}

echo "\n=======================================================\n";

/**
 * Busca un archivo recursivamente en un directorio
 */
function buscarArchivo($dir, $nombreArchivo) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $nombreArchivo) {
            return $file->getPathname();
        }
    }
    
    return null;
}

/**
 * Encuentra todos los archivos PDF en un directorio
 */
function encontrarTodosPDFs($dir) {
    $pdfs = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
            $pdfs[] = $file->getPathname();
        }
    }
    
    return $pdfs;
}
