<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

$filename = __DIR__ . '/../certificados/regla_coordenadas_NEW.pdf';

if (!file_exists($filename)) {
    die("❌ El archivo no existe: $filename\n");
}

try {
    $parser = new Parser();
    $pdf = $parser->parseFile($filename);
    $text = $pdf->getText();
    
    echo "=== Contenido del PDF ===\n";
    echo $text;
    echo "\n=========================\n";
    
} catch (Exception $e) {
    echo "Error al leer PDF: " . $e->getMessage();
}
