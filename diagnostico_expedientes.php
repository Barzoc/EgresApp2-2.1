<?php
/**
 * Diagnóstico completo de expedientes y Google Drive
 */

require_once __DIR__ . '/lib/PDFProcessor.php';
require_once __DIR__ . '/lib/GoogleDriveClient.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Diagnóstico de Expedientes</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
h2 { color: #555; margin-top: 30px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
.error-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
.success-box { background: #e8f5e9; padding: 10px; border-left: 4px solid #4CAF50; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #4CAF50; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
.cmd { background: #263238; color: #aed581; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; overflow-x: auto; }
</style></head><body><div class='container'>";

echo "<h1>🔍 Diagnóstico de Sistema - EGRESAPP2</h1>";
echo "<p><strong>Fecha y Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

// ========================================
// 1. VERIFICAR DEPENDENCIAS DE PROCESAMIENTO
// ========================================
echo "<h2>1️⃣ Dependencias de Procesamiento de PDFs</h2>";

$dependencies = PDFProcessor::checkDependencies();
$allOk = true;

echo "<table>";
echo "<tr><th>Dependencia</th><th>Estado</th><th>Detalles</th></tr>";

foreach ($dependencies as $name => $status) {
    $statusText = $status ? "<span class='success'>✓ Disponible</span>" : "<span class='error'>✗ No Disponible</span>";
    $details = "";
    
    if (!$status) {
        $allOk = false;
        switch($name) {
            case 'tesseract':
                $details = "Tesseract OCR no está en el PATH del sistema";
                break;
            case 'imagemagick':
                $details = "ImageMagick no está en el PATH del sistema";
                break;
            case 'pdf_to_text':
                $details = "pdftotext (Poppler) no está configurado correctamente";
                break;
            case 'python':
                $details = "Python no está configurado en config/pdf.php";
                break;
            case 'paddle_script':
                $details = "Script de PaddleOCR no encontrado";
                break;
        }
    }
    
    echo "<tr><td>{$name}</td><td>{$statusText}</td><td>{$details}</td></tr>";
}

echo "</table>";

// ========================================
// 2. VERIFICAR RUTAS EN CONFIGURACIÓN
// ========================================
echo "<h2>2️⃣ Configuración de Rutas</h2>";

$pdfConfig = file_exists(__DIR__ . '/config/pdf.php') ? require __DIR__ . '/config/pdf.php' : [];

echo "<table>";
echo "<tr><th>Configuración</th><th>Valor</th><th>Verificación</th></tr>";

$configChecks = [
    'pdftotext_path' => $pdfConfig['pdftotext_path'] ?? null,
    'python_path' => $pdfConfig['python_path'] ?? null,
    'poppler_path' => $pdfConfig['poppler_path'] ?? null,
];

foreach ($configChecks as $key => $value) {
    $verification = "";
    if ($value) {
        if (file_exists($value)) {
            $verification = "<span class='success'>✓ Existe</span>";
        } else {
            $verification = "<span class='error'>✗ No encontrado</span>";
            $allOk = false;
        }
    } else {
        $verification = "<span class='warning'>⚠ No configurado</span>";
    }
    
    echo "<tr><td>{$key}</td><td>" . htmlspecialchars($value ?? 'No configurado') . "</td><td>{$verification}</td></tr>";
}

echo "</table>";

// ========================================
// 3. PROBAR COMANDOS MANUALMENTE
// ========================================
echo "<h2>3️⃣ Prueba de Comandos</h2>";

echo "<div class='info'><strong>Nota:</strong> Probando si los comandos están disponibles en el PATH del sistema...</div>";

$commands = [
    'Tesseract' => 'tesseract --version 2>&1',
    'ImageMagick (magick)' => 'magick --version 2>&1',
    'ImageMagick (convert)' => 'convert --version 2>&1',
    'pdftotext' => 'pdftotext -v 2>&1',
];

echo "<table>";
echo "<tr><th>Comando</th><th>Estado</th><th>Salida</th></tr>";

foreach ($commands as $name => $cmd) {
    exec($cmd, $output, $returnCode);
    $status = $returnCode === 0 ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Error</span>";
    $outputText = implode("\n", array_slice($output, 0, 3));
    echo "<tr><td>{$name}</td><td>{$status}</td><td><pre style='margin:0;'>" . htmlspecialchars($outputText) . "</pre></td></tr>";
    unset($output);
}

echo "</table>";

// ========================================
// 4. GOOGLE DRIVE
// ========================================
echo "<h2>4️⃣ Google Drive</h2>";

try {
    $driveClient = new GoogleDriveClient();
    $driveEnabled = $driveClient->isEnabled();
    
    if ($driveEnabled) {
        echo "<div class='success-box'><strong>✓ Google Drive está habilitado</strong></div>";
        
        // Verificar archivo de credenciales
        $driveConfig = file_exists(__DIR__ . '/config/drive.php') ? require __DIR__ . '/config/drive.php' : [];
        $serviceAccountFile = $driveConfig['service_account_file'] ?? null;
        
        echo "<table>";
        echo "<tr><th>Configuración</th><th>Valor</th><th>Estado</th></tr>";
        
        $status = "";
        if ($serviceAccountFile && file_exists($serviceAccountFile)) {
            $status = "<span class='success'>✓ Archivo existe</span>";
        } else {
            $status = "<span class='error'>✗ Archivo no encontrado</span>";
            $allOk = false;
        }
        
        echo "<tr><td>Archivo de credenciales</td><td>" . htmlspecialchars($serviceAccountFile ?? 'No configurado') . "</td><td>{$status}</td></tr>";
        echo "<tr><td>Folder ID</td><td>" . htmlspecialchars($driveConfig['folder_id'] ?? 'No configurado') . "</td><td>-</td></tr>";
        echo "</table>";
        
        // Intentar conectar
        try {
            $client = $driveClient->getClient();
            echo "<div class='success-box'><strong>✓ Conexión exitosa con Google Drive</strong></div>";
        } catch (Exception $e) {
            echo "<div class='error-box'><strong>✗ Error al conectar:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            $allOk = false;
        }
        
    } else {
        echo "<div class='warning'><strong>⚠ Google Drive está deshabilitado</strong></div>";
    }
} catch (Exception $e) {
    echo "<div class='error-box'><strong>✗ Error al verificar Google Drive:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    $allOk = false;
}

// ========================================
// 5. RECOMENDACIONES
// ========================================
echo "<h2>5️⃣ Recomendaciones para Solucionar Problemas</h2>";

if (!$allOk) {
    echo "<div class='error-box'><strong>❌ Se encontraron problemas que deben ser solucionados</strong></div>";
    
    echo "<h3>Soluciones:</h3>";
    
    if (!$dependencies['tesseract']) {
        echo "<div class='info'>";
        echo "<strong>Para Tesseract:</strong><br>";
        echo "1. Verifica que Tesseract esté instalado<br>";
        echo "2. Agrega Tesseract al PATH del sistema:<br>";
        echo "<div class='cmd'>C:\\Program Files\\Tesseract-OCR</div>";
        echo "</div>";
    }
    
    if (!$dependencies['imagemagick']) {
        echo "<div class='info'>";
        echo "<strong>Para ImageMagick:</strong><br>";
        echo "1. Verifica que ImageMagick esté instalado<br>";
        echo "2. Agrega ImageMagick al PATH del sistema:<br>";
        echo "<div class='cmd'>C:\\Program Files\\ImageMagick-7.1.1-Q16-HDRI</div>";
        echo "</div>";
    }
    
    if (!$dependencies['pdf_to_text']) {
        echo "<div class='info'>";
        echo "<strong>Para Poppler (pdftotext):</strong><br>";
        echo "1. Verifica la ruta en config/pdf.php<br>";
        echo "2. Actual: <code>" . htmlspecialchars($pdfConfig['pdftotext_path'] ?? 'No configurado') . "</code><br>";
        echo "</div>";
    }
    
} else {
    echo "<div class='success-box'><strong>✅ ¡Todas las dependencias están correctamente configuradas!</strong></div>";
}

// ========================================
// 6. VARIABLES DE ENTORNO PATH
// ========================================
echo "<h2>6️⃣ Variables de Entorno PATH</h2>";

$path = getenv('PATH');
$pathEntries = explode(';', $path);

echo "<div class='info'><strong>Entradas en PATH del sistema:</strong></div>";
echo "<table>";
echo "<tr><th>#</th><th>Ruta</th><th>Tesseract?</th><th>ImageMagick?</th><th>Poppler?</th></tr>";

$foundTesseract = false;
$foundImageMagick = false;
$foundPoppler = false;

foreach ($pathEntries as $index => $entry) {
    $entry = trim($entry);
    if (empty($entry)) continue;
    
    $hasTesseract = (stripos($entry, 'tesseract') !== false) ? '✓' : '';
    $hasImageMagick = (stripos($entry, 'imagemagick') !== false) ? '✓' : '';
    $hasPoppler = (stripos($entry, 'poppler') !== false) ? '✓' : '';
    
    if ($hasTesseract) $foundTesseract = true;
    if ($hasImageMagick) $foundImageMagick = true;
    if ($hasPoppler) $foundPoppler = true;
    
    echo "<tr><td>" . ($index + 1) . "</td><td>" . htmlspecialchars($entry) . "</td><td>{$hasTesseract}</td><td>{$hasImageMagick}</td><td>{$hasPoppler}</td></tr>";
}

echo "</table>";

echo "<h3>Resumen de PATH:</h3>";
echo "<ul>";
echo "<li>Tesseract en PATH: " . ($foundTesseract ? "<span class='success'>✓ Sí</span>" : "<span class='error'>✗ No</span>") . "</li>";
echo "<li>ImageMagick en PATH: " . ($foundImageMagick ? "<span class='success'>✓ Sí</span>" : "<span class='error'>✗ No</span>") . "</li>";
echo "<li>Poppler en PATH: " . ($foundPoppler ? "<span class='success'>✓ Sí</span>" : "<span class='error'>✗ No</span>") . "</li>";
echo "</ul>";

if (!$foundTesseract || !$foundImageMagick) {
    echo "<div class='error-box'>";
    echo "<strong>⚠ ACCIÓN REQUERIDA:</strong> Debes agregar las rutas faltantes al PATH del sistema.<br>";
    echo "Ejecuta el script <code>SOLUCION_PATHS_DEPENDENCIAS.md</code> o configura manualmente el PATH.";
    echo "</div>";
}

echo "</div></body></html>";
