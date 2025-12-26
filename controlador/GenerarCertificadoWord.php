<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modelo/Utils.php';
require_once __DIR__ . '/../modelo/Egresado.php';
require_once __DIR__ . '/../modelo/Conexion.php';
require_once __DIR__ . '/../modelo/ConfiguracionCertificado.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

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
    $tituloFormateado = ucwords(mb_strtolower(trim($titulo), 'UTF-8'));

    // Obtener datos del firmante (Titular o Suplente)
    $configModel = new ConfiguracionCertificado(); // Ensure this class is required or available
    $firmanteDefault = $configModel->obtenerFirmante();
    
    $customFirmanteNombre = trim((string)($_POST['firmante_nombre'] ?? ''));
    $customFirmanteCargo = trim((string)($_POST['firmante_cargo'] ?? ''));
    
    // Si vienen datos en el POST (Suplente), usarlos. Si no, usar los de la BD (Titular)
    if ($customFirmanteNombre !== '' && $customFirmanteCargo !== '') {
        $nombreFirmante = $customFirmanteNombre;
        $cargoFirmante = $customFirmanteCargo;
    } else {
        $nombreFirmante = $firmanteDefault['nombre'];
        $cargoFirmante = $firmanteDefault['cargo'];
    }

    $nombreFirmante = mb_strtoupper($nombreFirmante, 'UTF-8');
    $cargoFirmante = mb_strtoupper($cargoFirmante, 'UTF-8');

    // Ruta de la plantilla Word
    $templatePath = __DIR__ . '/../certificados/MODELO CERTIFICADO TÍTULO.docx';
    
    if (!file_exists($templatePath)) {
        throw new RuntimeException('Plantilla Word no encontrada: ' . $templatePath);
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
    $timestamp = date('YmdHis');
    $wordFilename = sprintf('cert_%s_%s.docx', $cleanRut ?: 'egresado', $timestamp);
    $pdfFilename = sprintf('cert_%s_%s.pdf', $cleanRut ?: 'egresado', $timestamp);
    $wordPath = rtrim($certDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $wordFilename;
    $pdfPath = rtrim($certDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pdfFilename;

    // Cargar plantilla y reemplazar variables
    $templateProcessor = new TemplateProcessor($templatePath);
    
    // Reemplazar variables (ajusta según los nombres en tu Word)
    $templateProcessor->setValue('fecha_titulo', $fechaParrafo);
    $templateProcessor->setValue('nombre_completo', $nombreMayusculas);
    $templateProcessor->setValue('rut', $rutFormateado);
    $templateProcessor->setValue('titulo', $tituloFormateado);
    $templateProcessor->setValue('numero_registro', $numeroRegistro);
    $templateProcessor->setValue('fecha_emision', $fechaEmisionParrafo);
    
    // Variables del firmante
    $templateProcessor->setValue('nombre_firmante', $nombreFirmante);
    $templateProcessor->setValue('cargo_firmante', $cargoFirmante);

    // Guardar Word rellenado
    $templateProcessor->saveAs($wordPath);

    // Convertir Word a PDF usando LibreOffice (si está disponible)
    $pdfGenerated = false;
    
    // Intentar conversión con LibreOffice/soffice
    $sofficeCommands = [
        'soffice',
        '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"',
        '"C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe"',
    ];
    
    foreach ($sofficeCommands as $soffice) {
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
            $soffice,
            $certDir,
            $wordPath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($pdfPath)) {
            $pdfGenerated = true;
            // Eliminar el Word temporal
            @unlink($wordPath);
            break;
        }
    }

    if (!$pdfGenerated) {
        // Si no se pudo convertir a PDF, devolver el Word
        $finalFile = $wordPath;
        $finalFilename = $wordFilename;
        $message = 'Certificado generado en formato Word (LibreOffice no disponible para conversión a PDF).';
    } else {
        $finalFile = $pdfPath;
        $finalFilename = $pdfFilename;
        $message = 'Certificado generado correctamente.';
    }

    // Construir URL del certificado
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = rtrim(preg_replace('#/controlador/[^/]+$#', '', $scriptName), '/');
    if ($basePath === '') {
        $relativeUrl = '/certificados/' . $finalFilename;
    } else {
        $relativeUrl = $basePath . '/certificados/' . $finalFilename;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $absoluteUrl = $scheme . '://' . $host . $relativeUrl;

    // Obtener email del egresado
    $emailAddress = trim($registro['correo'] ?? $registro['email'] ?? '');
    $hasEmail = !empty($emailAddress) && filter_var($emailAddress, FILTER_VALIDATE_EMAIL);

    echo json_encode([
        'success' => true,
        'message' => $message,
        'url' => $absoluteUrl,
        'path' => $relativeUrl,
        'filename' => $finalFilename,
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
    $dia = (int) $date->format('d');
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
