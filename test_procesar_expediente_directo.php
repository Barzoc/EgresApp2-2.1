<?php
/**
 * Script para procesar un expediente PDF directamente desde el backend
 * Mide el tiempo de procesamiento
 */

// Iniciar medición de tiempo
$tiempoInicio = microtime(true);

echo "===========================================\n";
echo "PROCESAMIENTO DIRECTO DE EXPEDIENTE\n";
echo "===========================================\n\n";

// Archivo a procesar
$pdfPath = 'C:/Users/xerox/Desktop/EXPEDIENTES/LISTO PARA SUBIR/EDUARDO ANDRÉS GUERRERO TORRES.pdf';

echo "[1] Verificando archivo...\n";
if (!file_exists($pdfPath)) {
    die("ERROR: No se encontró el archivo en: $pdfPath\n");
}

$fileSize = filesize($pdfPath);
echo "    ✓ Archivo encontrado: " . basename($pdfPath) . "\n";
echo "    ✓ Tamaño: " . round($fileSize / 1024, 2) . " KB\n\n";

// Cargar dependencias
echo "[2] Cargando dependencias...\n";
$t1 = microtime(true);

require_once __DIR__ . '/modelo/ExpedienteQueue.php';
require_once __DIR__ . '/services/ExpedienteProcessor.php';
require_once __DIR__ . '/lib/GoogleDriveClient.php';
require_once __DIR__ . '/lib/DriveFolderMapper.php';

$t2 = microtime(true);
echo "    ✓ Dependencias cargadas en " . round(($t2 - $t1) * 1000, 2) . " ms\n\n";

// Copiar archivo a carpeta de uploads
echo "[3] Preparando archivo para procesamiento...\n";
$t3 = microtime(true);

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    echo "    ✓ Directorio uploads creado\n";
}

$originalName = basename($pdfPath);
$timestamp = date('YmdHis');
$filename = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
$destino = $uploadsDir . '/' . $filename;

if (!copy($pdfPath, $destino)) {
    die("ERROR: No se pudo copiar el archivo a uploads\n");
}

$t4 = microtime(true);
echo "    ✓ Archivo copiado a: $filename\n";
echo "    ✓ Tiempo de copia: " . round(($t4 - $t3) * 1000, 2) . " ms\n\n";

// Crear entrada en la cola
echo "[4] Creando entrada en cola de procesamiento...\n";
$t5 = microtime(true);

$idExpediente = null; // Will be assigned by processor after inserting into DB
$queue = new ExpedienteQueue();
$queueId = $queue->enqueue([
    'filename' => $filename,
    'filepath' => $destino,
    'id_expediente' => $idExpediente,
    'drive_id' => null,
    'drive_link' => null,
]);

$t6 = microtime(true);
echo "    ✓ Queue ID: $queueId\n";
echo "    ✓ Tiempo de registro: " . round(($t6 - $t5) * 1000, 2) . " ms\n\n";

// Procesar expediente
echo "[5] PROCESANDO EXPEDIENTE (OCR + Extracción de datos)...\n";
echo "    Esta es la parte que más tarda...\n\n";
$t7 = microtime(true);

$processor = new ExpedienteProcessor($queue);
$processResult = $processor->processJobById($queueId);

$t8 = microtime(true);
$tiempoProcesamiento = ($t8 - $t7);

echo "    ✓ Procesamiento completado\n";
echo "    ⏱  TIEMPO DE PROCESAMIENTO: " . round($tiempoProcesamiento, 2) . " segundos\n";
echo "    ⏱  (" . round($tiempoProcesamiento * 1000, 2) . " ms)\n\n";

// Mostrar resultados
echo "===========================================\n";
echo "RESULTADOS DEL PROCESAMIENTO\n";
echo "===========================================\n\n";

if ($processResult['success']) {
    echo "✓ ÉXITO: Expediente procesado correctamente\n\n";

    if (isset($processResult['fields'])) {
        echo "DATOS EXTRAÍDOS:\n";
        echo "----------------\n";
        foreach ($processResult['fields'] as $campo => $valor) {
            echo sprintf("%-20s: %s\n", ucfirst($campo), $valor ?: '(no disponible)');
        }
        echo "\n";
    }

    if (isset($processResult['id_expediente'])) {
        echo "ID Expediente guardado: " . $processResult['id_expediente'] . "\n";
    }

    echo "\nESTADO: " . ($processResult['estado'] ?? 'desconocido') . "\n";
} else {
    echo "✗ ERROR: " . ($processResult['mensaje'] ?? 'Error desconocido') . "\n\n";

    if (isset($processResult['payload'])) {
        echo "DEBUG INFO:\n";
        print_r($processResult['payload']);
        echo "\n";
    }
}

// Tiempo total
$tiempoFin = microtime(true);
$tiempoTotal = ($tiempoFin - $tiempoInicio);

echo "\n===========================================\n";
echo "RESUMEN DE TIEMPOS\n";
echo "===========================================\n";
echo "Carga de dependencias:  " . round(($t2 - $t1) * 1000, 2) . " ms\n";
echo "Copia de archivo:       " . round(($t4 - $t3) * 1000, 2) . " ms\n";
echo "Registro en cola:       " . round(($t6 - $t5) * 1000, 2) . " ms\n";
echo "PROCESAMIENTO OCR:      " . round($tiempoProcesamiento, 2) . " s (" . round($tiempoProcesamiento * 1000, 2) . " ms)\n";
echo "-------------------------------------------\n";
echo "TIEMPO TOTAL:           " . round($tiempoTotal, 2) . " s (" . round($tiempoTotal * 1000, 2) . " ms)\n";
echo "===========================================\n\n";

echo "Nota: El archivo procesado quedó guardado en:\n";
echo "$destino\n";
