<?php
/**
 * Script Avanzado: Verificador de MOTORES OCR
 * Verifica si los programas realmente responden en la consola
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🛠️ Verificador de Motores OCR (Ejecución Real)</h1>";
echo "<p>Usuario detectado: <strong>" . get_current_user() . "</strong></p>";

function probarComando($nombre, $cmd) {
    echo "<h3>Probando: $nombre</h3>";
    echo "<div style='background:#f5f5f5; padding:10px; border:1px solid #ddd'>";
    
    $output = [];
    $ret = -1;
    // Forzamos captura de STDERR también
    exec("$cmd 2>&1", $output, $ret);
    
    if ($ret === 0) {
        echo "<span style='color:green; font-weight:bold'>✅ INSTALADO Y RESPONDIENDO</span><br>";
        echo "<pre>" . implode("\n", array_slice($output, 0, 5)) . "</pre>"; // Mostrar solo primeras 5 líneas
    } else {
        echo "<span style='color:red; font-weight:bold'>❌ NO DETECTADO O ERROR</span><br>";
        echo "Código retorno: $ret<br>";
        echo "Salida:<br><pre>" . implode("\n", $output) . "</pre>";
        
        if ($nombre === 'Tesseract') {
            echo "<p style='color:blue'>💡 <strong>Solución:</strong> Reinstala Tesseract y asegúrate de marcar 'Add to PATH'. O agrega 'C:\Program Files\Tesseract-OCR' a la variable de entorno PATH.</p>";
        }
    }
    echo "</div>";
}

// 1. Probar Poppler (ya sabemos que está config, pero probemos ejecución)
$config = require __DIR__ . '/config/pdf.php';
$pdftotext = $config['pdftotext_path'] ?? 'pdftotext';
probarComando("Poppler (pdftotext)", "\"$pdftotext\" -v");

// 2. Probar Tesseract (CRÍTICO para imágenes)
probarComando("Tesseract OCR", "tesseract --version");

// 3. Probar ImageMagick (Necesario para Tesseract)
probarComando("ImageMagick (convert)", "convert --version");

// 4. Probar Ghostscript (Opcional)
probarComando("Ghostscript", "gs --version");
