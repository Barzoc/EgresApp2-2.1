<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../modelo/ConfiguracionCertificado.php';

$model = new ConfiguracionCertificado();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

switch ($accion) {
    case 'obtener':
        $firmante = $model->obtenerFirmante();
        echo json_encode([
            'success' => true,
            'data' => $firmante,
        ]);
        break;

    case 'guardar':
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $cargo = trim((string) ($_POST['cargo'] ?? ''));

        if ($nombre === '' || $cargo === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Debe completar nombre y cargo.']);
            break;
        }

        $ok = $model->guardarFirmante($nombre, $cargo);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Firmante actualizado correctamente.' : 'No se pudo guardar la configuración.'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no soportada.']);
        break;
}
