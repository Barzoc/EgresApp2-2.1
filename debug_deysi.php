<?php
require_once __DIR__ . '/lib/PDFProcessor.php';

$file = 'C:/laragon/www/EGRESAPP2/assets/expedientes/expedientes_subidos/DEYSI_DE_LAS_MERCEDES_VILLEGAS_URRA.pdf';

if (!file_exists($file)) {
    die("File not found\n");
}

echo "Procesando $file ...\n";
$data = PDFProcessor::extractStructuredData($file);

echo "\n--- Campos Extraidos ---\n";
print_r($data['fields']);

echo "\n--- Texto Raw (Primeros 1000 chars) ---\n";
echo substr($data['text'], 0, 1000);
