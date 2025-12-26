<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../modelo/Usuario.php';

$usuarioModel = new Usuario();
$funcion = $_POST['funcion'] ?? '';

if ($funcion === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Función no especificada']);
    exit;
}

function sanitize($value)
{
    return trim((string) $value);
}

switch ($funcion) {
    case 'listar':
        try {
            $usuarios = $usuarioModel->listar();
            echo json_encode($usuarios);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudieron listar los usuarios', 'detalle' => $e->getMessage()]);
        }
        break;

    case 'crear':
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if ($nombre === '' || $email === '' || $contrasena === '') {
            http_response_code(400);
            echo json_encode(['status' => 'invalido', 'mensaje' => 'Todos los campos son obligatorios']);
            break;
        }

        $result = $usuarioModel->crear($nombre, $email, $contrasena);
        echo json_encode($result);
        break;

    case 'actualizar':
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if ($id <= 0 || $nombre === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['status' => 'invalido']);
            break;
        }

        $result = $usuarioModel->actualizar($id, $nombre, $email);
        echo json_encode($result);
        break;

    case 'eliminar':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'invalido']);
            break;
        }
        $result = $usuarioModel->eliminar($id);
        echo json_encode($result);
        break;

    case 'cambiar_password':
        $id = (int) ($_POST['id'] ?? 0);
        $contrasena = $_POST['contrasena'] ?? '';
        $contrasenaActual = $_POST['contrasena_actual'] ?? '';
        $forzar = filter_var($_POST['forzar'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($id <= 0 || $contrasena === '') {
            http_response_code(400);
            echo json_encode(['status' => 'invalido']);
            break;
        }
        if (!$forzar) {
            if ($contrasenaActual === '' || !$usuarioModel->verificarContrasena($id, $contrasenaActual)) {
                http_response_code(400);
                echo json_encode(['status' => 'invalido', 'mensaje' => 'La contraseña actual no es correcta']);
                break;
            }
        }

        $result = $usuarioModel->actualizarContrasena($id, $contrasena);
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Función no soportada']);
        break;
}
