<?php
/**
 * Script de Diagnóstico OCR para EGRESAPP2
 * Verifica todas las dependencias necesarias para el procesamiento de expedientes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

function checkCommand($command, $versionFlag = '--version') {
    $fullCommand = $command . ' ' . $versionFlag . ' 2>&1';
    $output = shell_exec($fullCommand);
    return [
        'available' => !empty($output) && stripos($output, 'not found') === false && stripos($output, 'not recognized') === false,
        'output' => trim($output ?? 'No disponible')
    ];
}

function checkPHPExtension($extension) {
    return extension_loaded($extension);
}

function checkFile($path) {
    return file_exists($path);
}

function getStatus($isOk) {
    return $isOk ? '✅' : '❌';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico OCR - EGRESAPP2</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        .section-header {
            background: #f5f5f5;
            padding: 15px 20px;
            font-size: 1.3em;
            font-weight: bold;
            border-bottom: 2px solid #e0e0e0;
        }
        .section-content {
            padding: 20px;
        }
        .check-item {
            display: grid;
            grid-template-columns: 50px 300px 1fr;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            align-items: start;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status {
            font-size: 2em;
            text-align: center;
        }
        .check-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .check-details {
            font-size: 0.9em;
            color: #666;
            font-family: 'Courier New', monospace;
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 150px;
        }
        .summary {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary.success {
            background: #d4edda;
            border-color: #28a745;
        }
        .summary.error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .summary h2 {
            margin-bottom: 15px;
        }
        .solution {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .solution h4 {
            color: #1976D2;
            margin-bottom: 10px;
        }
        .solution pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .config-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnóstico OCR - EGRESAPP2</h1>
            <p>Verificación de dependencias para procesamiento de expedientes</p>
        </div>
        
        <div class="content">
            <?php
            $allOk = true;
            $missing = [];
            
            // 1. Verificar extensiones PHP
            echo '<div class="section">';
            echo '<div class="section-header">📦 Extensiones PHP Requeridas</div>';
            echo '<div class="section-content">';
            
            $phpExtensions = [
                'gd' => 'Procesamiento de imágenes',
                'fileinfo' => 'Detección de tipos de archivo',
                'mbstring' => 'Manejo de cadenas multibyte (UTF-8)',
                'json' => 'Procesamiento de datos JSON'
            ];
            
            foreach ($phpExtensions as $ext => $desc) {
                $available = checkPHPExtension($ext);
                if (!$available) {
                    $allOk = false;
                    $missing[] = "Extensión PHP: $ext";
                }
                echo '<div class="check-item">';
                echo '<div class="status">' . getStatus($available) . '</div>';
                echo '<div class="check-name">' . $ext . '</div>';
                echo '<div class="check-details">' . $desc . '</div>';
                echo '</div>';
            }
            
            echo '</div></div>';
            
            // 2. Verificar herramientas de línea de comandos
            echo '<div class="section">';
            echo '<div class="section-header">🛠️ Herramientas de Línea de Comandos</div>';
            echo '<div class="section-content">';
            
            $commands = [
                'tesseract' => ['flag' => '--version', 'desc' => 'Motor OCR principal'],
                'convert' => ['flag' => '--version', 'desc' => 'ImageMagick - Conversión de PDF a imágenes'],
                'pdftotext' => ['flag' => '-v', 'desc' => 'Poppler - Extracción de texto de PDF'],
                'gs' => ['flag' => '--version', 'desc' => 'Ghostscript - Procesamiento de PDF']
            ];
            
            foreach ($commands as $cmd => $info) {
                $result = checkCommand($cmd, $info['flag']);
                if (!$result['available']) {
                    $allOk = false;
                    $missing[] = "Comando: $cmd";
                }
                echo '<div class="check-item">';
                echo '<div class="status">' . getStatus($result['available']) . '</div>';
                echo '<div class="check-name">' . $cmd . '</div>';
                echo '<div class="check-details">';
                echo '<strong>' . $info['desc'] . '</strong><br>';
                echo htmlspecialchars(substr($result['output'], 0, 200));
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div></div>';
            
            // 3. Verificar archivos de configuración
            echo '<div class="section">';
            echo '<div class="section-header">📄 Archivos del Sistema</div>';
            echo '<div class="section-content">';
            
            $files = [
                'lib/PDFProcessor.php' => 'Clase principal de procesamiento OCR',
                'vendor/autoload.php' => 'Autoload de Composer',
                'config/pdf_config.php' => 'Configuración de PDF/OCR (opcional)'
            ];
            
            foreach ($files as $file => $desc) {
                $path = __DIR__ . '/' . $file;
                $exists = checkFile($path);
                echo '<div class="check-item">';
                echo '<div class="status">' . getStatus($exists) . '</div>';
                echo '<div class="check-name">' . $file . '</div>';
                echo '<div class="check-details">' . $desc . '</div>';
                echo '</div>';
            }
            
            echo '</div></div>';
            
            // 4. Verificar Composer packages
            echo '<div class="section">';
            echo '<div class="section-header">📚 Paquetes de Composer</div>';
            echo '<div class="section-content">';
            
            require_once __DIR__ . '/vendor/autoload.php';
            
            $packages = [
                'Smalot\\PdfParser\\Parser' => 'smalot/pdfparser - Parser de PDF',
                'Spatie\\PdfToText\\Pdf' => 'spatie/pdf-to-text - Extractor de texto'
            ];
            
            foreach ($packages as $class => $desc) {
                $available = class_exists($class);
                if (!$available) {
                    $allOk = false;
                    $missing[] = "Paquete: $desc";
                }
                echo '<div class="check-item">';
                echo '<div class="status">' . getStatus($available) . '</div>';
                echo '<div class="check-name">' . $class . '</div>';
                echo '<div class="check-details">' . $desc . '</div>';
                echo '</div>';
            }
            
            echo '</div></div>';
            
            // 5. Test rápido de OCR
            echo '<div class="section">';
            echo '<div class="section-header">🧪 Prueba de Funcionalidad OCR</div>';
            echo '<div class="section-content">';
            
            $testResult = ['available' => false, 'message' => 'No se pudo realizar la prueba'];
            
            if (class_exists('PDFProcessor')) {
                try {
                    // Buscar un PDF de prueba
                    $testPdf = __DIR__ . '/assets/expedientes/expedientes_subidos/tecnico-en-administracion';
                    if (is_dir($testPdf)) {
                        $pdfs = glob($testPdf . '/*.pdf');
                        if (!empty($pdfs)) {
                            $testFile = $pdfs[0];
                            $testResult['available'] = true;
                            $testResult['message'] = 'PDFProcessor disponible - Archivo de prueba: ' . basename($testFile);
                        }
                    }
                } catch (Exception $e) {
                    $testResult['message'] = 'Error: ' . $e->getMessage();
                }
            }
            
            echo '<div class="check-item">';
            echo '<div class="status">' . getStatus($testResult['available']) . '</div>';
            echo '<div class="check-name">PDFProcessor</div>';
            echo '<div class="check-details">' . htmlspecialchars($testResult['message']) . '</div>';
            echo '</div>';
            
            echo '</div></div>';
            
            // Resumen y soluciones
            if ($allOk) {
                echo '<div class="summary success">';
                echo '<h2>✅ Sistema Totalmente Funcional</h2>';
                echo '<p>Todas las dependencias están instaladas correctamente. El sistema OCR debería funcionar sin problemas.</p>';
                echo '</div>';
            } else {
                echo '<div class="summary error">';
                echo '<h2>❌ Se Encontraron Problemas</h2>';
                echo '<p><strong>Dependencias faltantes:</strong></p>';
                echo '<ul style="margin-left: 20px; margin-top: 10px;">';
                foreach ($missing as $item) {
                    echo '<li>' . htmlspecialchars($item) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
                
                // Mostrar soluciones
                echo '<div class="section">';
                echo '<div class="section-header">🔧 Soluciones Recomendadas</div>';
                echo '<div class="section-content">';
                
                echo '<div class="solution">';
                echo '<h4>1. Instalar Tesseract OCR</h4>';
                echo '<p>Descarga e instala desde: <a href="https://github.com/UB-Mannheim/tesseract/wiki" target="_blank">Tesseract Windows Installer</a></p>';
                echo '<pre>Versión recomendada: tesseract-ocr-w64-setup-5.3.0.exe
Incluir idioma: Spanish (spa.traineddata)
Agregar al PATH del sistema</pre>';
                echo '</div>';
                
                echo '<div class="solution">';
                echo '<h4>2. Instalar ImageMagick</h4>';
                echo '<p>Descarga e installa desde: <a href="https://imagemagick.org/script/download.php#windows" target="_blank">ImageMagick Downloads</a></p>';
                echo '<pre>Versión recomendada: ImageMagick-7.x-Q16-HDRI-x64-dll.exe
Marcar: "Install legacy utilities (e.g. convert)"
Agregar al PATH del sistema</pre>';
                echo '</div>';
                
                echo '<div class="solution">';
                echo '<h4>3. Instalar Poppler (pdftotext)</h4>';
                echo '<p>Descarga desde: <a href="https://github.com/oschwartz10612/poppler-windows/releases/" target="_blank">Poppler for Windows</a></p>';
                echo '<pre>1. Descargar Release-XX.XX.X-0.zip
2. Extraer en C:\\poppler (o similar)
3. Agregar C:\\poppler\\Library\\bin al PATH</pre>';
                echo '</div>';
                
                echo '<div class="solution">';
                echo '<h4>4. Instalar dependencias de Composer</h4>';
                echo '<p>Ejecuta en la carpeta del proyecto:</p>';
                echo '<pre>composer install</pre>';
                echo '</div>';
                
                echo '<div class="solution">';
                echo '<h4>5. Script Automático de Instalación</h4>';
                echo '<p>Si tienes el instalador maestro, ejecuta:</p>';
                echo '<pre>InstalarDependencias.bat</pre>';
                echo '<p>O manualmente:</p>';
                echo '<pre>powershell -ExecutionPolicy Bypass -File InstalarDependencias.ps1</pre>';
                echo '</div>';
                
                echo '</div></div>';
            }
            
            // Información del sistema
            echo '<div class="section">';
            echo '<div class="section-header">💻 Información del Sistema</div>';
            echo '<div class="section-content">';
            echo '<div class="config-info">';
            echo '<strong>PHP Version:</strong> ' . PHP_VERSION . '<br>';
            echo '<strong>Sistema Operativo:</strong> ' . PHP_OS . '<br>';
            echo '<strong>Directorio del proyecto:</strong> ' . __DIR__ . '<br>';
            echo '<strong>PATH del sistema:</strong><br>';
            echo '<pre style="background: white; padding: 10px; max-height: 200px; overflow-y: auto;">' . htmlspecialchars(getenv('PATH')) . '</pre>';
            echo '</div>';
            echo '</div></div>';
            ?>
            
            <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 10px;">
                <p><strong>💡 Tip:</strong> Después de instalar las dependencias, reinicia el servidor web y vuelve a ejecutar este diagnóstico.</p>
                <p style="margin-top: 10px;">
                    <a href="?" style="display: inline-block; background: #667eea; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;">🔄 Actualizar Diagnóstico</a>
                    <a href="test_captura_expediente.php" style="display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;">✅ Probar Extracción OCR</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
