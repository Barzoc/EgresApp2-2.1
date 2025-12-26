<?php
/**
 * Archivo de prueba para verificar la generación de certificados HTML
 * Acceder desde: http://localhost/EGRESAPP2/test_certificado_html.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/modelo/Utils.php';
require_once __DIR__ . '/modelo/Egresado.php';
require_once __DIR__ . '/lib/HtmlToPdfConverter.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Certificado HTML</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .result.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">🧪 Prueba de Generación de Certificado HTML</h2>
        
        <form id="testForm" method="POST">
            <div class="form-group">
                <label for="rut">RUT del Egresado:</label>
                <input type="text" class="form-control" id="rut" name="rut" 
                       placeholder="Ej: 12345678-9" required>
                <small class="form-text text-muted">
                    Ingresa el RUT de un egresado existente en la base de datos
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Generar Certificado
            </button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['rut'])) {
            $rut = trim($_POST['rut']);
            
            echo '<div class="result">';
            
            if (!Utils::validarRut($rut)) {
                echo '<div class="alert alert-danger">❌ RUT inválido</div>';
            } else {
                try {
                    $egresadoModel = new Egresado();
                    $registro = $egresadoModel->ObtenerDatosCertificadoPorRut($rut);

                    if (!$registro) {
                        echo '<div class="alert alert-warning">⚠️ No se encontraron datos para el RUT proporcionado.</div>';
                    } else {
                        // Preparar datos
                        $titulo = $registro['tituloObtenido']
                            ?? $registro['titulo_nombre']
                            ?? $registro['titulo_catalogo']
                            ?? '';

                        $fechaTitulo = $registro['fechaGrado']
                            ?? ($registro['fecha_grado'] ?? $registro['fechaEntregaCertificado'] ?? null);

                        $numeroRegistro = $registro['numeroCertificado']
                            ?? ($registro['numero_documento'] ?? '');

                        $config = is_file(__DIR__ . '/config/certificado.php')
                            ? require __DIR__ . '/config/certificado.php'
                            : [];

                        // Formatear datos
                        $fechaTituloFormateada = formatearFechaLarga($fechaTitulo);
                        $fechaEmisionFormateada = formatearFechaLarga(date('Y-m-d'));
                        $rutFormateado = formatearRut($registro['carnet'] ?? $rut);
                        $logoPath = resolveLogoPath();
                        
                        $templateData = [
                            'nombre_completo' => mb_strtoupper(trim($registro['nombreCompleto'] ?? ''), 'UTF-8'),
                            'rut' => $rutFormateado,
                            'titulo' => mb_strtoupper(trim($titulo), 'UTF-8'),
                            'fecha_titulo' => $fechaTituloFormateada,
                            'numero_registro' => $numeroRegistro ?: '____________________',
                            'fecha_emision' => $fechaEmisionFormateada,
                            'rector' => mb_strtoupper(trim($config['rector'] ?? 'RECTOR(A)'), 'UTF-8'),
                            'logo_path' => $logoPath,
                        ];

                        // Generar PDF
                        $templatePath = __DIR__ . '/templates/certificado_template.html';
                        
                        if (!file_exists($templatePath)) {
                            throw new RuntimeException('Plantilla de certificado no encontrada.');
                        }

                        $certDir = __DIR__ . '/certificados';
                        if (!is_dir($certDir)) {
                            mkdir($certDir, 0755, true);
                        }

                        $cleanRut = preg_replace('/[^0-9kK]/', '', $registro['carnet'] ?? $rut);
                        $filename = sprintf('cert_%s_%s.pdf', $cleanRut ?: 'egresado', date('YmdHis'));
                        $filePath = $certDir . DIRECTORY_SEPARATOR . $filename;

                        $converter = new HtmlToPdfConverter();
                        $success = $converter->convertTemplateAndSave($templatePath, $templateData, $filePath);

                        if ($success) {
                            $relativeUrl = 'certificados/' . $filename;
                            echo '<div class="alert alert-success">';
                            echo '<h5>✅ Certificado generado exitosamente</h5>';
                            echo '<p><strong>Datos del egresado:</strong></p>';
                            echo '<ul>';
                            echo '<li><strong>Nombre:</strong> ' . htmlspecialchars($templateData['nombre_completo']) . '</li>';
                            echo '<li><strong>RUT:</strong> ' . htmlspecialchars($templateData['rut']) . '</li>';
                            echo '<li><strong>Título:</strong> ' . htmlspecialchars($templateData['titulo']) . '</li>';
                            echo '<li><strong>Fecha de Título:</strong> ' . htmlspecialchars($templateData['fecha_titulo']) . '</li>';
                            echo '<li><strong>N° Registro:</strong> ' . htmlspecialchars($templateData['numero_registro']) . '</li>';
                            echo '</ul>';
                            echo '<p><a href="' . $relativeUrl . '" target="_blank" class="btn btn-success">';
                            echo '<i class="fas fa-download"></i> Ver Certificado PDF</a></p>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-danger">❌ Error al generar el PDF</div>';
                        }
                    }
                } catch (Throwable $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<h5>❌ Error:</h5>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
        }

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

        function resolveLogoPath(): string
        {
            $candidates = [
                __DIR__ . '/assets/img/imagenes/LOGO LICEO.png',
                __DIR__ . '/assets/img/imagenes/logo liceo.png',
                __DIR__ . '/assets/img/logo.png',
            ];

            foreach ($candidates as $path) {
                if (is_file($path)) {
                    return $path;
                }
            }

            return __DIR__ . '/assets/img/imagenes/LOGO LICEO.png';
        }
        ?>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
