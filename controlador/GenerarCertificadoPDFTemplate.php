<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modelo/Utils.php';
require_once __DIR__ . '/../modelo/Egresado.php';
require_once __DIR__ . '/../modelo/Conexion.php';
require_once __DIR__ . '/../modelo/ConfiguracionCertificado.php';
require_once __DIR__ . '/../lib/PdfTemplateFiller.php';

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

    // Preparar datos
    $nombreCompleto = $registro['nombreCompleto'] ?? $registro['nombrecompleto'] ?? '';

    $configModel = new ConfiguracionCertificado();
    $firmante = $configModel->obtenerFirmante();
    $customFirmanteNombre = trim((string)($_POST['firmante_nombre'] ?? ''));
    $customFirmanteCargo = trim((string)($_POST['firmante_cargo'] ?? ''));
    if ($customFirmanteNombre !== '' && $customFirmanteCargo !== '') {
        $firmante['nombre'] = $customFirmanteNombre;
        $firmante['cargo'] = $customFirmanteCargo;
    }
    $titulo = $registro['tituloObtenido']
        ?? $registro['tituloobtenido']
        ?? $registro['titulo_catalogo']
        ?? $registro['titulocatalogo']
        ?? '';
    
    $fechaTitulo = $registro['fechaGrado']
        ?? $registro['fechagrado']
        ?? $registro['fechaEntregaCertificado']
        ?? $registro['fechaentregacertificado']
        ?? null;
    
    $numeroRegistro = $registro['numeroCertificado']
        ?? $registro['numerocertificado']
        ?? $registro['numero_documento']
        ?? '';

    // Formatear datos
    $fechaParrafo = formatearFechaParrafo($fechaTitulo);
    $fechaEmisionParrafo = formatearFechaParrafo(date('Y-m-d'));
    $rutFormateado = formatearRut($registro['carnet'] ?? $rut);
    $nombreMayusculas = mb_strtoupper(trim($nombreCompleto), 'UTF-8');
    $titulo = ucwords(mb_strtolower(trim($titulo), 'UTF-8'));

    // Ruta del PDF base limpio (sin líneas)
    $templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';
    
    if (!file_exists($templatePath)) {
        throw new RuntimeException('PDF plantilla no encontrado: ' . $templatePath);
    }

    // Crear directorio de certificados si no existe
    $certDir = realpath(__DIR__ . '/../certificados');
    if ($certDir === false) {
        $certDir = __DIR__ . '/../certificados';
        if (!is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }
    }

    // Generar nombre de archivo con el nombre del egresado
    $cleanRut = preg_replace('/[^0-9kK]/', '', $registro['carnet'] ?? $rut);
    
    // Limpiar el nombre para usarlo en el archivo (sin caracteres especiales)
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombreCompleto);
    $nombreLimpio = preg_replace('/\s+/', '_', trim($nombreLimpio));
    $nombreLimpio = substr($nombreLimpio, 0, 50); // Limitar longitud
    
    $filename = sprintf('Certificado_%s_%s.pdf', $nombreLimpio ?: $cleanRut, date('YmdHis'));
    $filePath = rtrim($certDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    // Coordenadas convertidas de Inkscape (px) a TCPDF (mm) - Factor: 0.2646 mm/px
    $data = [
        // Fecha: Inkscape X=602.93 Y=492.77
        ['text' => $fechaParrafo, 'x' => 159.52, 'y' => 130.38, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
        
        // Nombre: Inkscape X=656.04 Y=531.13
        ['text' => $nombreMayusculas . ' ,', 'x' => 173.57, 'y' => 140.54, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        
        // RUT: Inkscape X=277.37 Y=569.49
        ['text' => $rutFormateado . ' ,', 'x' => 73.39, 'y' => 150.69, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        
        // Título: Inkscape X=675.71 Y=568.50
        ['text' => $titulo . '.', 'x' => 178.79, 'y' => 150.43, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
        
        // Registro: Inkscape X=543.91 Y=683.58
        ['text' => $numeroRegistro . '.', 'x' => 143.92, 'y' => 180.87, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        
        // Emisión: Inkscape X=706.20 Y=800.63
        ['text' => $fechaEmisionParrafo . '.', 'x' => 186.86, 'y' => 211.85, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
        
        // Firma: Nombre
        ['text' => mb_strtoupper($firmante['nombre'] ?? 'RECTOR(A)', 'UTF-8'), 'x' => 105, 'y' => 245, 'w' => 70, 'h' => 0, 'size' => 11, 'align' => 'C', 'style' => 'B'],
        
        // Firma: Cargo
        ['text' => mb_strtoupper($firmante['cargo'] ?? 'RECTOR(A)', 'UTF-8'), 'x' => 105, 'y' => 252, 'w' => 70, 'h' => 0, 'size' => 10, 'align' => 'C'],
    ];

    // Rellenar PDF
    $filler = new PdfTemplateFiller();
    $success = $filler->fillTemplate($templatePath, $filePath, $data);

    if (!$success) {
        throw new RuntimeException('No se pudo generar el certificado PDF.');
    }

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

    // Obtener email del egresado
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
 * Formatea una fecha para usar en párrafos (formato: "8 de Junio de 2010")
 */
function formatearFechaParrafo(?string $fecha): string
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
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    $mes = $meses[(int) $date->format('n') - 1];
    $dia = (int) $date->format('d'); // Sin ceros a la izquierda
    $anio = $date->format('Y');
    
    return sprintf('%d de %s de %s', $dia, $mes, $anio);
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
