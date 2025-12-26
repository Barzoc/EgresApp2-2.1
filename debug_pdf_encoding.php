<?php
// debug_pdf_encoding.php
require_once 'lib/HtmlToPdfConverter.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba de Generación de PDF con Caracteres Especiales</h1>";

try {
    $converter = new HtmlToPdfConverter();

    // Test strings with special characters
    $testString = "ÁÉÍÓÚ Ñ áéíóú ñ";
    $testName = "ADRIÁN VÍCTOR ANDRÉS YÁÑEZ ROJAS";
    $testTitle = "Técnico de Nivel Medio En Administración";

    $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test PDF</title>
    <style>
        body { font-family: 'dejavusans', sans-serif; }
    </style>
</head>
<body>
    <h1>Prueba de Caracteres</h1>
    <p>Juego de caracteres: <strong>$testString</strong></p>
    <p>Nombre de prueba: <strong>$testName</strong></p>
    <p>Título de prueba: <strong>$testTitle</strong></p>
    <p>Si puedes leer esto correctamente, la generación de PDF funciona bien con UTF-8.</p>
</body>
</html>
HTML;

    // Generate PDF
    $pdfContent = $converter->convertHtmlToPdf($html);

    // Save to file
    $outputFile = __DIR__ . '/test_encoding.pdf';
    file_put_contents($outputFile, $pdfContent);

    echo "<p>PDF generado exitosamente en: $outputFile</p>";
    echo "<p><a href='test_encoding.pdf' target='_blank'>Ver PDF Generado</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>