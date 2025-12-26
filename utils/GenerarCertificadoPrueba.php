<?php
// Script para generar certificado de prueba desde terminal
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/PdfTemplateFiller.php';

// Datos de prueba
$fechaParrafo = '8 de Julio de 2010';
$nombreMayusculas = 'ALLAN CHRISTIAN RAMIREZ CASTRO';
$rutFormateado = '17.829.702-3';
$titulo = 'Técnico De Nivel Medio En Administración';
$numeroRegistro = '15-359';
$fechaEmisionParrafo = '26 de Noviembre de 2025';

// Ruta del PDF base
$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';

if (!file_exists($templatePath)) {
    die("❌ Error: PDF plantilla no encontrado en: $templatePath\n");
}

// Generar nombre de archivo
$filename = 'cert_prueba_' . date('YmdHis') . '.pdf';
$filePath = __DIR__ . '/../certificados/' . $filename;

// Coordenadas ajustadas
$data = [
    ['text' => $fechaParrafo, 'x' => 118, 'y' => 106, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    ['text' => $nombreMayusculas . ' ,', 'x' => 70, 'y' => 121, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    ['text' => $rutFormateado . ' ,', 'x' => 35, 'y' => 130, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    ['text' => $titulo . '.', 'x' => 85, 'y' => 130, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    ['text' => $numeroRegistro . '.', 'x' => 118, 'y' => 157, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
    ['text' => $fechaEmisionParrafo . '.', 'x' => 118, 'y' => 183, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
];

echo "📄 Generando certificado de prueba...\n";
echo "Plantilla: $templatePath\n";
echo "Salida: $filePath\n\n";

// Rellenar PDF
$filler = new PdfTemplateFiller();
$success = $filler->fillTemplate($templatePath, $filePath, $data);

if ($success) {
    echo "✅ Certificado generado exitosamente!\n";
    echo "📁 Ubicación: $filePath\n";
    echo "🌐 URL: http://localhost/EGRESAPP2/certificados/$filename\n";
} else {
    echo "❌ Error al generar el certificado\n";
}
