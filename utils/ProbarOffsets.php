<?php
// Script para probar diferentes offsets de coordenadas Y
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
    die("❌ Error: PDF plantilla no encontrado\n");
}

// Probar diferentes offsets de Y
$offsetsToTest = [-10, -5, 0, 5, 10, 15, 20];

echo "🔬 Generando certificados de prueba con diferentes offsets Y...\n\n";

foreach ($offsetsToTest as $offset) {
    $filename = sprintf('cert_offset_%s_%s.pdf', ($offset >= 0 ? 'plus' : 'minus') . abs($offset), date('YmdHis'));
    $filePath = __DIR__ . '/../certificados/' . $filename;
    
    // Coordenadas base + offset
    $data = [
        ['text' => $fechaParrafo, 'x' => 118, 'y' => 112 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
        ['text' => $nombreMayusculas . ' ,', 'x' => 70, 'y' => 121 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        ['text' => $rutFormateado . ' ,', 'x' => 35, 'y' => 130 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        ['text' => $titulo . '.', 'x' => 85, 'y' => 130 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
        ['text' => $numeroRegistro . '.', 'x' => 118, 'y' => 157 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L', 'style' => 'B'],
        ['text' => $fechaEmisionParrafo . '.', 'x' => 118, 'y' => 183 + $offset, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],
    ];
    
    $filler = new PdfTemplateFiller();
    $success = $filler->fillTemplate($templatePath, $filePath, $data);
    
    if ($success) {
        $offsetStr = ($offset >= 0 ? '+' : '') . $offset;
        echo "✅ Offset Y=$offsetStr: http://localhost/EGRESAPP2/certificados/$filename\n";
    } else {
        $offsetStr = ($offset >= 0 ? '+' : '') . $offset;
        echo "❌ Offset Y=$offsetStr: Error\n";
    }
}

echo "\n📋 Abre cada PDF y encuentra cuál tiene la mejor alineación.\n";
echo "Luego dime qué offset funcionó mejor y actualizaré el código.\n";
