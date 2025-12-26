<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/EmailSender.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$rut = trim($_POST['rut'] ?? '');
$pdfPath = trim($_POST['pdf_path'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($rut) || empty($pdfPath) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Construir ruta completa del PDF
$fullPath = __DIR__ . '/../' . ltrim($pdfPath, './');

if (!is_file($fullPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Certificado no encontrado']);
    exit;
}

try {
    $emailSender = new EmailSender();
    $sent = $emailSender->sendCertificate($email, $nombre, $fullPath);
    
    if ($sent) {
        echo json_encode([
            'success' => true,
            'message' => 'Certificado enviado exitosamente a ' . $email,
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar el correo. Intente nuevamente.',
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar correo: ' . $e->getMessage(),
    ]);
}
