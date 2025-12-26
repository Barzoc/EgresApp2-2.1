<?php
// Script con coordenadas EXACTAS medidas con la regla visual
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/PdfTemplateFiller.php';

$fechaParrafo = '8 de Julio de 2010';
$nombreMayusculas = 'ALLAN CHRISTIAN RAMIREZ CASTRO';
$rutFormateado = '17.829.702-3';
$titulo = 'Técnico De Nivel Medio En Administración';
$numeroRegistro = '15-359';
$fechaEmisionParrafo = '26 de Noviembre de 2025';

$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';

if (!file_exists($templatePath)) {
    die("❌ Error: PDF plantilla no encontrado\n");
}

$filename = 'cert_FINAL_VERIFICADO_' . date('YmdHis') . '.pdf';
$filePath = __DIR__ . '/../certificados/' . $filename;

// Coordenadas CALIBRADAS FINAL (Factor de conversión aplicado)
$data = [
    // Fecha del título: Y=128
    ['text' => $fechaParrafo, 'x' => 118, 'y' => 128, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    
    // Nombre completo: Y=135.5
    ['text' => $nombreMayusculas . ' ,', 'x' => 70, 'y' => 135.5, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // RUT: Y=142
    ['text' => $rutFormateado . ' ,', 'x' => 35, 'y' => 142, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // Título: Y=142
    ['text' => $titulo . '.', 'x' => 85, 'y' => 142, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    
    // Número de registro: Y=172
    ['text' => $numeroRegistro . '.', 'x' => 118, 'y' => 172, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // Fecha de emisión: Y=208
    ['text' => $fechaEmisionParrafo . '.', 'x' => 118, 'y' => 208, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
];

$filler = new PdfTemplateFiller();
$success = $filler->fillTemplate($templatePath, $filePath, $data);

if ($success) {
    echo "✅ Certificado FINAL generado!\n";
    echo "📁 $filePath\n";
    echo "🌐 http://localhost/EGRESAPP2/certificados/$filename\n";
} else {
    echo "❌ Error al generar certificado\n";
}
