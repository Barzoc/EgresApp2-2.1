<?php

require_once __DIR__ . '/../modelo/ExpedienteQueue.php';
require_once __DIR__ . '/../services/ExpedienteProcessor.php';
require_once __DIR__ . '/../lib/GoogleDriveClient.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';
require_once __DIR__ . '/../lib/DriveSync.php';

header('Content-Type: application/json; charset=utf-8');

try {
    session_start();
} catch (Exception $e) {
    // Ignorar si las cabeceras ya fueron enviadas (por ejemplo en CLI)
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$idExpediente = $_POST['id_expediente'] ?? null;
// Convertir strings vacíos a null para evitar errores en columnas INTEGER
$idExpediente = empty($idExpediente) ? null : $idExpediente;
$driveFileIdInput = $_POST['drive_file_id'] ?? ($_POST['expediente_drive_id'] ?? null);
$driveLinkInput = $_POST['drive_file_link'] ?? ($_POST['expediente_drive_link'] ?? null);
$importContext = $_POST['import_context'] ?? null;

$hasUpload = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;
$sourceType = $hasUpload ? 'local' : 'drive';

if (!$hasUpload && empty($driveFileIdInput)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'mensaje' => 'No se recibió el archivo PDF ni un ID de Google Drive']);
    exit;
}

if ($hasUpload) {
    $fileTmp = $_FILES['file']['tmp_name'];
    $mime = function_exists('mime_content_type') ? mime_content_type($fileTmp) : ($_FILES['file']['type'] ?? null);
    $extension = strtolower(pathinfo($_FILES['file']['name'] ?? '', PATHINFO_EXTENSION));
    $allowedMimes = ['application/pdf', 'application/x-pdf', 'application/octet-stream'];

    if (!in_array($mime, $allowedMimes, true) && $extension !== 'pdf') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'mensaje' => 'El archivo debe ser un PDF válido',
            'detalles' => ['mime_detectado' => $mime, 'extension' => $extension]
        ]);
        exit;
    }
}

$uploadsDir = realpath(__DIR__ . '/../assets/expedientes/expedientes_subidos');
if ($uploadsDir === false) {
    $uploadsDir = __DIR__ . '/../assets/expedientes/expedientes_subidos';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
}

$originalName = $hasUpload ? ($_FILES['file']['name'] ?? 'expediente.pdf') : null;
$downloadedTempPath = null;
$driveClient = null;

if (!$hasUpload) {
    try {
        $driveClient = new GoogleDriveClient();
        if (!$driveClient->isEnabled()) {
            throw new RuntimeException('Google Drive no está habilitado para importar expedientes.');
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo inicializar el cliente de Google Drive: ' . $e->getMessage()]);
        exit;
    }

    $metadata = $driveClient->getFileMetadata($driveFileIdInput);
    if (!$metadata) {
        http_response_code(404);
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró el archivo en Google Drive o no es accesible.']);
        exit;
    }

    if (!empty($metadata['mimeType']) && $metadata['mimeType'] === 'application/vnd.google-apps.folder') {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'La referencia seleccionada corresponde a una carpeta de Google Drive, no a un archivo descargable.']);
        exit;
    }

    $originalName = $metadata['name'] ?? ($driveFileIdInput . '.pdf');
    $downloadedTempPath = tempnam(sys_get_temp_dir(), 'drive_exp_');
    if (!$driveClient->downloadFile($driveFileIdInput, $downloadedTempPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo descargar el archivo desde Google Drive.']);
        exit;
    }
    $driveLinkInput = $driveLinkInput ?: ($metadata['webViewLink'] ?? $metadata['webContentLink'] ?? null);
}

$extension = pathinfo($originalName ?? '', PATHINFO_EXTENSION);
$baseName = pathinfo($originalName ?? 'expediente.pdf', PATHINFO_FILENAME);

if (empty($extension)) {
    $extension = 'pdf';
}

$safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', $baseName);
if ($safeBase === '') {
    $safeBase = 'expediente';
}

$filename = $safeBase . '.' . strtolower($extension);
$counter = 1;
while (file_exists(rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename)) {
    $filename = $safeBase . '_' . $counter . '.' . strtolower($extension);
    $counter++;
}
$destino = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

$moveSucceeded = false;
if ($hasUpload) {
    $moveSucceeded = move_uploaded_file($_FILES['file']['tmp_name'], $destino);
} else {
    $moveSucceeded = rename($downloadedTempPath, $destino);
    if (!$moveSucceeded && $downloadedTempPath) {
        $moveSucceeded = copy($downloadedTempPath, $destino);
        @unlink($downloadedTempPath);
    }
}

if (!$moveSucceeded) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No se pudo guardar el archivo en el servidor']);
    exit;
}

$driveId = null;
$driveLink = null;
$driveWarnings = [];
$shouldUploadToDrive = $hasUpload;
$driveFolderTarget = DriveFolderMapper::resolveByTitle($_POST['titulo_extraido'] ?? null);

if (!$hasUpload) {
    $driveId = $driveFileIdInput;
    $driveLink = $driveLinkInput;
    $shouldUploadToDrive = false;
}

if ($shouldUploadToDrive) {
    try {
        $driveClient = $driveClient ?: new GoogleDriveClient();
        if ($driveClient->isEnabled()) {
            $uploadResult = $driveClient->uploadFile($destino, $filename, 'application/pdf', $driveFolderTarget['drive_folder_id'] ?? null);
            $driveId = $uploadResult['id'] ?? null;
            $driveLink = $uploadResult['webViewLink'] ?? $uploadResult['webContentLink'] ?? null;
        }
    } catch (Throwable $e) {
        $driveWarnings[] = 'No se pudo subir a Google Drive: ' . $e->getMessage();
    }
}

$queue = new ExpedienteQueue();
$queueId = $queue->enqueue([
    'filename' => $filename,
    'filepath' => $destino,
    'id_expediente' => $idExpediente,
    'drive_id' => $driveId,
    'drive_link' => $driveLink,
]);

$processor = new ExpedienteProcessor($queue);
$processResult = $processor->processJobById($queueId);

if (!$processResult['success']) {
    $mensaje = $processResult['mensaje'] ?? 'Error al procesar el expediente.';
    $statusCode = 500;
    $esDuplicado = false;
    if (stripos($mensaje, 'Ya existe un egresado') !== false || stripos($mensaje, 'ya fue procesado') !== false) {
        $statusCode = 409;
        $esDuplicado = true;
    } elseif (stripos($mensaje, 'Expediente ya se encuentra ingresado') !== false || stripos($mensaje, 'Estos datos ya han sido ingresados') !== false) {
        $statusCode = 409;
        $esDuplicado = true;
    } elseif (stripos($mensaje, 'OCR incompleto') !== false) {
        // OCR incompleto: retornar SUCCESS con datos parciales para edición manual
        $partialFields = $processResult['fields'] ?? [];
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'mensaje' => 'El OCR extrajo solo algunos campos. Por favor, completa los datos faltantes manualmente.',
            'datos' => [
                'nombre' => $partialFields['nombre'] ?? '',
                'rut' => $partialFields['rut'] ?? '',
                'numero_certificado' => $partialFields['numero_certificado'] ?? '',
                'titulo' => $partialFields['titulo'] ?? '',
                'fecha_entrega' => $partialFields['fecha_entrega'] ?? '',
                'sexo' => $partialFields['sexo'] ?? '',
                'anio_egreso' => $partialFields['anio_egreso'] ?? ''
            ],
            'archivo' => $filename,
            'queue_id' => $queueId,
            'estado' => 'pending_manual_completion',
            'fuentes' => ['drive' => !empty($driveId)],
            'debug' => $processResult['payload']['ocr'] ?? null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($esDuplicado && is_file($destino)) {
        @unlink($destino);
    }

    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'mensaje' => $mensaje,
        'queue_id' => $queueId,
        'estado' => 'failed',
        'debug' => $processResult['payload']['ocr'] ?? null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$fields = $processResult['fields'] ?? [];
$finalFilename = $processResult['payload']['filename'] ?? $filename;
$finalFilepath = $processResult['payload']['filepath'] ?? $destino;
$finalDriveId = $processResult['drive_info']['drive_id'] ?? $driveId;

// Reubicar el archivo en la carpeta correcta de Drive según el título extraído
$tituloDetectado = $fields['titulo'] ?? $fields['titulo_obtenido'] ?? $fields['especialidad'] ?? null;
$carpetaDetectada = DriveFolderMapper::resolveByTitle($tituloDetectado);
if (!empty($carpetaDetectada['drive_folder_id']) && !empty($finalDriveId)) {
    try {
        $driveClient = $driveClient ?: new GoogleDriveClient();
        if ($driveClient->isEnabled()) {
            $driveClient->moveFileToFolder($finalDriveId, $carpetaDetectada['drive_folder_id']);
        }
    } catch (Throwable $e) {
        $driveWarnings[] = 'No se pudo reubicar el archivo en la carpeta correspondiente de Drive: ' . $e->getMessage();
    }
}

// Trigger automatic sync in background
try {
    triggerDriveSync();
} catch (Throwable $e) {
    error_log('Auto-sync failed: ' . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'mensaje' => 'Expediente procesado correctamente',
    'queue_id' => $queueId,
    'estado' => 'done',
    'archivo' => $finalFilename,
    'egresado_id' => $processResult['id_expediente'] ?? null,
    'drive_link' => $driveLink,
    'drive_id' => $driveId,
    'warnings' => $driveWarnings,
    'fuentes' => [
        'local' => [
            'disponible' => is_file($finalFilepath),
            'archivo' => $finalFilename,
            'origen' => $sourceType
        ],
        'drive' => [
            'disponible' => !empty($driveId),
            'drive_id' => $driveId,
            'drive_link' => $driveLink,
            'origen' => $shouldUploadToDrive ? 'subida_local' : 'drive_existente'
        ]
    ],
    'datos' => [
        'rut' => $fields['rut'] ?? '',
        'nombre' => $fields['nombre'] ?? $fields['nombre_completo'] ?? '',
        'fecha_egreso' => $fields['fecha_egreso'] ?? $fields['anio_egreso'] ?? '',
        'fecha_entrega' => $fields['fecha_entrega'] ?? '',
        'numero_certificado' => $fields['numero_certificado'] ?? '',
        'titulo' => $fields['titulo'] ?? $fields['especialidad'] ?? ''
    ],
    'import_context' => $importContext,
    'source' => $sourceType,
    'debug' => $processResult['payload']['ocr'] ?? null
], JSON_UNESCAPED_UNICODE);
