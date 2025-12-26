<?php
// Script con coordenadas ajustadas manualmente basándose en el offset +15
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

$filename = 'cert_ajustado_final_' . date('YmdHis') . '.pdf';
$filePath = __DIR__ . '/../certificados/' . $filename;

// Coordenadas ajustadas basándose en el offset +15 que funcionó mejor
// Ahora ajustando también las X para cada campo
$data = [
    // Fecha: después de "certifica que con fecha"
    ['text' => $fechaParrafo, 'x' => 118, 'y' => 127, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    
    // Nombre: después de "se le confirió a Don (ña)"  
    ['text' => $nombreMayusculas . ' ,', 'x' => 70, 'y' => 136, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // RUT: después de "RUT"
    ['text' => $rutFormateado . ' ,', 'x' => 35, 'y' => 145, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // Título: después de "el título de"
    ['text' => $titulo . '.', 'x' => 85, 'y' => 145, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    
    // Número de registro: después de "registrado con el N°"
    ['text' => $numeroRegistro . '.', 'x' => 118, 'y' => 172, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    
    // Fecha de emisión: después de "Se emite el presente certificado con fecha"
    ['text' => $fechaEmisionParrafo . '.', 'x' => 118, 'y' => 198, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
];

$filler = new PdfTemplateFiller();
$success = $filler->fillTemplate($templatePath, $filePath, $data);

if ($success) {
    echo "✅ Certificado ajustado generado!\n";
    echo "📁 $filePath\n";
    echo "🌐 http://localhost/EGRESAPP2/certificados/$filename\n";
} else {
    echo "❌ Error al generar certificado\n";
}
