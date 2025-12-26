<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modelo/Utils.php';
require_once __DIR__ . '/../modelo/Egresado.php';
require_once __DIR__ . '/../modelo/Conexion.php';
require_once __DIR__ . '/../lib/HtmlToPdfConverter.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$rut = trim($_POST['rut'] ?? '');
if (empty($rut) || !Utils::validarRut($rut)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'RUT inválido']);
    exit;
}

try {
    // Obtener datos del egresado
    $egresadoModel = new Egresado();
    $registro = $egresadoModel->ObtenerDatosCertificadoPorRut($rut);

    if (!$registro) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos para el RUT proporcionado.']);
        exit;
    }

    // Preparar datos para la plantilla (manejar claves en minúsculas por PDO::CASE_LOWER)
    $titulo = $registro['tituloObtenido']
        ?? $registro['tituloobtenido']
        ?? $registro['titulo_nombre']
        ?? $registro['titulo_catalogo']
        ?? $registro['titulocatalogo']
        ?? '';

    $fechaTitulo = $registro['fechaGrado']
        ?? $registro['fechagrado']
        ?? ($registro['fecha_grado'] ?? $registro['fechaEntregaCertificado'] ?? $registro['fechaentregacertificado'] ?? null);

    $numeroRegistro = $registro['numeroCertificado']
        ?? $registro['numerocertificado']
        ?? ($registro['numero_documento'] ?? '');

    // Cargar configuración
    $config = is_file(__DIR__ . '/../config/certificado.php')
        ? require __DIR__ . '/../config/certificado.php'
        : [];

    // Formatear fecha de título
    $fechaTituloFormateada = formatearFechaLarga($fechaTitulo);
    
    // Formatear fecha de emisión
    $fechaEmisionFormateada = formatearFechaLarga(date('Y-m-d'));
    
    // Formatear RUT
    $rutFormateado = formatearRut($registro['carnet'] ?? $rut);
    
    // Preparar ruta del logo
    $logoPath = resolveLogoPath();
    
    // Datos para reemplazar en la plantilla
    $nombreCompleto = $registro['nombreCompleto'] ?? $registro['nombrecompleto'] ?? '';
    
    $templateData = [
        'nombre_completo' => mb_strtoupper(trim($nombreCompleto), 'UTF-8'),
        'rut' => $rutFormateado,
        'titulo' => mb_strtoupper(trim($titulo), 'UTF-8'),
        'fecha_titulo' => $fechaTituloFormateada,
        'numero_registro' => $numeroRegistro ?: '____________________',
        'fecha_emision' => $fechaEmisionFormateada,
        'rector' => mb_strtoupper(trim($config['rector'] ?? 'RECTOR(A)'), 'UTF-8'),
        'logo_path' => $logoPath,
    ];

    // Ruta de la plantilla
    $templatePath = __DIR__ . '/../templates/certificado_template.html';
    
    if (!file_exists($templatePath)) {
        throw new RuntimeException('Plantilla de certificado no encontrada.');
    }

    // Crear directorio de certificados si no existe
    $certDir = realpath(__DIR__ . '/../certificados');
    if ($certDir === false) {
        $certDir = __DIR__ . '/../certificados';
        if (!is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }
    }

    // Generar nombre de archivo
    $cleanRut = preg_replace('/[^0-9kK]/', '', $registro['carnet'] ?? $rut);
    $filename = sprintf('cert_%s_%s.pdf', $cleanRut ?: 'egresado', date('YmdHis'));
    $filePath = rtrim($certDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    // Convertir HTML a PDF
    $converter = new HtmlToPdfConverter();
    $success = $converter->convertTemplateAndSave($templatePath, $templateData, $filePath);

    if (!$success) {
        throw new RuntimeException('No se pudo generar el certificado PDF.');
    }

    // Actualizar ruta del certificado en la base de datos
    // NOTA: Comentado temporalmente porque la columna rutaCertificado no existe en la tabla
    /*
    try {
        $conexion = new Conexion();
        $pdo = $conexion->pdo;
        $stmt = $pdo->prepare('UPDATE tituloegresado SET rutaCertificado = :ruta WHERE identificacion = :id LIMIT 1');
        $stmt->execute([
            ':ruta' => $filename,
            ':id' => $registro['identificacion'] ?? 0,
        ]);
    } catch (Throwable $e) {
        // No bloquear por errores en la actualización de la ruta
        error_log("Error al actualizar rutaCertificado: " . $e->getMessage());
    }
    */

    // Construir URL del certificado
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = rtrim(preg_replace('#/controlador/[^/]+$#', '', $scriptName), '/');
    if ($basePath === '') {
        $relativeUrl = '/certificados/' . $filename;
    } else {
        $relativeUrl = $basePath . '/certificados/' . $filename;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $absoluteUrl = $scheme . '://' . $host . $relativeUrl;

    // Obtener email del egresado para mostrar opción de envío
    $emailAddress = trim($registro['correo'] ?? $registro['email'] ?? '');
    $hasEmail = !empty($emailAddress) && filter_var($emailAddress, FILTER_VALIDATE_EMAIL);

    echo json_encode([
        'success' => true,
        'message' => 'Certificado generado correctamente.',
        'url' => $absoluteUrl,
        'path' => $relativeUrl,
        'filename' => $filename,
        'has_email' => $hasEmail,
        'email_address' => $hasEmail ? $emailAddress : null,
        'rut' => $rut,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el certificado: ' . $e->getMessage(),
    ]);
}

/**
 * Formatea una fecha en formato largo en español
 */
function formatearFechaLarga(?string $fecha): string
{
    if (!$fecha) {
        return '____________________';
    }

    try {
        $date = new DateTime($fecha);
    } catch (Throwable $e) {
        return '____________________';
    }

    $meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];
    
    $mes = $meses[(int) $date->format('n') - 1];
    $dia = str_pad($date->format('d'), 2, '0', STR_PAD_LEFT);
    $anio = $date->format('Y');
    
    return sprintf('%s DE %s DE %s', $dia, mb_strtoupper($mes, 'UTF-8'), $anio);
}

/**
 * Formatea un RUT chileno
 */
function formatearRut(string $rut): string
{
    $clean = preg_replace('/[^0-9kK]/', '', $rut);
    if (strlen($clean) < 2) {
        return $rut;
    }
    
    $dv = strtoupper(substr($clean, -1));
    $digits = strrev(substr($clean, 0, -1));
    $formatted = [];
    
    for ($i = 0; $i < strlen($digits); $i++) {
        if ($i > 0 && $i % 3 === 0) {
            $formatted[] = '.';
        }
        $formatted[] = $digits[$i];
    }
    
    return strrev(implode('', $formatted)) . '-' . $dv;
}

/**
 * Resuelve la ruta del logo del liceo
 */
function resolveLogoPath(): string
{
    $candidates = [
        __DIR__ . '/../assets/img/imagenes/LOGO LICEO.png',
        __DIR__ . '/../assets/img/imagenes/logo liceo.png',
        __DIR__ . '/../assets/img/logo.png',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    // Retornar ruta por defecto aunque no exista
    return __DIR__ . '/../assets/img/imagenes/LOGO LICEO.png';
}
