<?php
// Script de prueba para verificar que LibreOffice funciona
require_once __DIR__ . '/../lib/WordToPdfConverter.php';

echo "=== Test de Conversión Word → PDF ===\n\n";

// Verificar que existe la plantilla
$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.docx';

if (!file_exists($templatePath)) {
    die("❌ Plantilla no encontrada: $templatePath\n");
}

echo "✅ Plantilla encontrada\n";

// Crear directorio de prueba
$testDir = __DIR__ . '/../temp';
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

// Copiar plantilla para prueba
$testWordPath = $testDir . '/test_certificado.docx';
copy($templatePath, $testWordPath);

echo "✅ Archivo de prueba creado\n";

// Intentar conversión
$converter = new WordToPdfConverter();
$pdfPath = $converter->convertToPdf($testWordPath, $testDir);

if ($pdfPath) {
    echo "✅ Conversión exitosa!\n";
    echo "📁 PDF generado en: $pdfPath\n";
    echo "🌐 Tamaño: " . filesize($pdfPath) . " bytes\n";
    
    // Limpiar archivos de prueba
    $converter->cleanup([$testWordPath, $pdfPath]);
    echo "✅ Archivos de prueba eliminados\n";
} else {
    echo "❌ Error en la conversión\n";
    echo "Verifica que LibreOffice esté instalado correctamente.\n";
}
