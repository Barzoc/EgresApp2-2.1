<?php
/**
 * Script de prueba para captura de datos OCR de expedientes
 * Usa PDFProcessor para extraer datos estructurados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

// Cargar autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PDFProcessor.php';

// Función para imprimir resultados en HTML formateado
function printResult($title, $data) {
    echo "<div style='margin: 20px; padding: 15px; border: 2px solid #333; border-radius: 8px; background: #f5f5f5;'>";
    echo "<h2 style='color: #2c3e50; margin-top: 0;'>📄 {$title}</h2>";
    
    if (is_array($data)) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        foreach ($data as $key => $value) {
            $displayKey = ucfirst(str_replace('_', ' ', $key));
            $displayValue = is_array($value) ? '<pre>' . print_r($value, true) . '</pre>' : htmlspecialchars($value ?? '(vacío)');
            
            echo "<tr style='border-bottom: 1px solid #ddd;'>";
            echo "<td style='padding: 10px; font-weight: bold; width: 200px; background: #e8e8e8;'>{$displayKey}</td>";
            echo "<td style='padding: 10px;'>{$displayValue}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<pre style='background: white; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($data) . "</pre>";
    }
    
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Captura OCR - Expedientes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .info {
            background: #3498db;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Prueba de Captura OCR - Expedientes</h1>
        
        <?php
        // Buscar un expediente PDF para probar
        $expedientesDir = __DIR__ . '/assets/expedientes/expedientes_subidos/tecnico-en-administracion';
        
        if (!is_dir($expedientesDir)) {
            echo "<div class='error'>❌ Directorio de expedientes no encontrado: {$expedientesDir}</div>";
            exit;
        }
        
        // Buscar específicamente el expediente de YUDITH CARMEN QUISPE MORALES
        $targetFile = 'YUDITH_CARMEN_QUISPE_MORALES.pdf';
        $pdfPath = $expedientesDir . '/' . $targetFile;
        
        if (!file_exists($pdfPath)) {
            // Buscar variaciones del nombre
            $pdfFiles = glob($expedientesDir . '/*QUISPE_MORALES*.pdf');
            if (empty($pdfFiles)) {
                echo "<div class='error'>❌ No se encontró el expediente de YUDITH CARMEN QUISPE MORALES</div>";
                echo "<div class='info'>Buscando: {$targetFile}</div>";
                exit;
            }
            $pdfPath = $pdfFiles[0];
        }
        
        $fileName = basename($pdfPath);
        
        // Obtener todos los archivos para listar al final
        $pdfFiles = glob($expedientesDir . '/*.pdf');
        
        echo "<div class='info'>";
        echo "📁 <strong>Archivo seleccionado:</strong> {$fileName}<br>";
        echo "📂 <strong>Ruta completa:</strong> {$pdfPath}<br>";
        echo "📊 <strong>Tamaño:</strong> " . round(filesize($pdfPath) / 1024, 2) . " KB";
        echo "</div>";
        
        // Iniciar cronómetro
        $startTime = microtime(true);
        
        echo "<h3>⏳ Procesando expediente...</h3>";
        flush();
        
        try {
            // Extraer datos estructurados
            $result = PDFProcessor::extractStructuredData($pdfPath);
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            echo "<div class='success'>✅ Extracción completada en {$executionTime} segundos</div>";
            
            // Mostrar resultados
            if (!empty($result['fields'])) {
                printResult('Datos Extraídos', $result['fields']);
            } else {
                echo "<div class='error'>⚠️ No se pudieron extraer campos estructurados</div>";
            }
            
            // Mostrar información adicional
            echo "<div style='margin-top: 20px;'>";
            echo "<h3>📋 Información de Procesamiento</h3>";
            echo "<table style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='border-bottom: 1px solid #ddd;'>";
            echo "<td style='padding: 10px; font-weight: bold; background: #e8e8e8;'>Método de extracción</td>";
            echo "<td style='padding: 10px;'>" . ($result['source'] ?? 'desconocido') . "</td>";
            echo "</tr>";
            echo "<tr style='border-bottom: 1px solid #ddd;'>";
            echo "<td style='padding: 10px; font-weight: bold; background: #e8e8e8;'>Líneas detectadas</td>";
            echo "<td style='padding: 10px;'>" . count($result['lines'] ?? []) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='padding: 10px; font-weight: bold; background: #e8e8e8;'>Longitud del texto</td>";
            echo "<td style='padding: 10px;'>" . strlen($result['text'] ?? '') . " caracteres</td>";
            echo "</tr>";
            echo "</table>";
            echo "</div>";
            
            // Mostrar texto extraído (primeros 500 caracteres)
            if (!empty($result['text'])) {
                $textPreview = substr($result['text'], 0, 500);
                echo "<div style='margin-top: 20px;'>";
                echo "<h3>📝 Vista Previa del Texto Extraído</h3>";
                echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 6px; overflow-x: auto; max-height: 300px;'>";
                echo htmlspecialchars($textPreview);
                if (strlen($result['text']) > 500) {
                    echo "\n\n... (texto truncado, " . (strlen($result['text']) - 500) . " caracteres más)";
                }
                echo "</pre>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            echo "<div class='error'>";
            echo "❌ Error durante la extracción (después de {$executionTime} segundos):<br>";
            echo "<strong>" . htmlspecialchars($e->getMessage()) . "</strong><br>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }
        
        // Listar todos los expedientes disponibles
        echo "<div style='margin-top: 30px; padding: 20px; background: #ecf0f1; border-radius: 8px;'>";
        echo "<h3>📚 Expedientes Disponibles (" . count($pdfFiles) . " archivos)</h3>";
        echo "<ol style='column-count: 2; column-gap: 20px;'>";
        foreach ($pdfFiles as $pdf) {
            $name = basename($pdf);
            $size = round(filesize($pdf) / 1024, 2);
            echo "<li style='margin-bottom: 5px;'>{$name} <small>({$size} KB)</small></li>";
        }
        echo "</ol>";
        echo "</div>";
        ?>
        
    </div>
</body>
</html>
