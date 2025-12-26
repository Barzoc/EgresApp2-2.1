<?php
// Script de prueba para generar un certificado con la plantilla Word
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modelo/Egresado.php';

use PhpOffice\PhpWord\TemplateProcessor;

echo "=== Generando Certificado de Prueba con Word ===\n\n";

// Datos de prueba
$datos = [
    'fecha_titulo' => '8 de Julio de 2010',
    'nombre_completo' => 'ALLAN CHRISTIAN RAMIREZ CASTRO',
    'rut' => '17.829.702-3',
    'titulo' => 'Técnico De Nivel Medio En Administración',
    'numero_registro' => '15-359',
    'fecha_emision' => '26 de Noviembre de 2025'
];

// Ruta de la plantilla
$templatePath = __DIR__ . '/../certificados/MODELO CERTIFICADO TÍTULO.docx';

if (!file_exists($templatePath)) {
    die("❌ Plantilla no encontrada: $templatePath\n");
}

echo "✅ Plantilla encontrada\n";

// Cargar plantilla
$templateProcessor = new TemplateProcessor($templatePath);

// Reemplazar placeholders
foreach ($datos as $key => $value) {
    $templateProcessor->setValue($key, $value);
    echo "  → Reemplazando \${$key} = $value\n";
}

// Guardar Word temporal
$tempDir = __DIR__ . '/../temp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$wordFilename = 'cert_prueba_word_' . date('YmdHis') . '.docx';
$wordPath = $tempDir . '/' . $wordFilename;
$templateProcessor->saveAs($wordPath);

echo "\n✅ Word generado: $wordPath\n";

// Convertir a PDF
$libreOfficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
$certDir = __DIR__ . '/../certificados';

$command = sprintf(
    '"%s" --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
    $libreOfficePath,
    $certDir,
    $wordPath
);

echo "🔄 Convirtiendo a PDF...\n";
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    $pdfFilename = str_replace('.docx', '.pdf', $wordFilename);
    $pdfPath = $certDir . '/' . $pdfFilename;
    
    if (file_exists($pdfPath)) {
        echo "✅ PDF generado exitosamente!\n";
        echo "📁 $pdfPath\n";
        echo "🌐 http://localhost/EGRESAPP2/certificados/$pdfFilename\n";
        
        // Limpiar Word temporal
        unlink($wordPath);
        echo "✅ Archivo Word temporal eliminado\n";
    } else {
        echo "❌ PDF no fue generado\n";
    }
} else {
    echo "❌ Error en conversión. Código: $returnCode\n";
    echo "Output: " . implode("\n", $output) . "\n";
}
