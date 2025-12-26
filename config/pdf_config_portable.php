<?php
/**
 * Configuración PORTABLE de rutas OCR para EGRESAPP2
 * Este archivo usa rutas RELATIVAS y detección automática
 * Copiar este archivo al otro PC como "pdf.php"
 */

// Detectar usuario actual automáticamente
$currentUser = getenv('USERNAME') ?: getenv('USER');

// Rutas comunes de instalación de Poppler (buscar en orden)
$popplerPaths = [
    'C:/poppler/Library/bin',
    'C:/Program Files/poppler/Library/bin',
    "C:/Users/{$currentUser}/AppData/Local/Microsoft/WinGet/Packages/oschwartz10612.Poppler_Microsoft.Winget.Source_8wekyb3d8bbwe/poppler-25.07.0/Library/bin",
];

// Rutas comunes de Python
$pythonPaths = [
    'C:/Python310/python.exe',
    'C:/Python311/python.exe',
    'C:/Python312/python.exe',
    "C:/Users/{$currentUser}/AppData/Local/Programs/Python/Python310/python.exe",
    "C:/Users/{$currentUser}/AppData/Local/Programs/Python/Python311/python.exe",
    "C:/Users/{$currentUser}/AppData/Local/Programs/Python/Python312/python.exe",
];

// Buscar Poppler
$popplerPath = null;
$pdftotextPath = null;
foreach ($popplerPaths as $path) {
    if (file_exists($path . '/pdftotext.exe')) {
        $popplerPath = $path;
        $pdftotextPath = $path . '/pdftotext.exe';
        break;
    }
}

// Buscar Python
$pythonPath = null;
foreach ($pythonPaths as $path) {
    if (file_exists($path)) {
        $pythonPath = $path;
        break;
    }
}

// Buscar ImageMagick (convert/magick)
$imPaths = [
    'C:/Program Files/ImageMagick-7.1.1-Q16-HDRI/magick.exe',
    'C:/Program Files/ImageMagick-7.1.1-Q16/magick.exe',
    'C:/Program Files/ImageMagick-7.1.0-Q16-HDRI/magick.exe', 
    'C:/Program Files/ImageMagick-7.0.10-Q16/magick.exe',
];
// Auto-detectar cualquier version de ImageMagick 7
$programFiles = getenv('ProgramFiles') ?: 'C:/Program Files';
$dirs = glob($programFiles . '/ImageMagick-7*');
if ($dirs) {
    foreach ($dirs as $dir) {
        $imPaths[] = $dir . '/magick.exe';
        $imPaths[] = $dir . '/convert.exe';
    }
}

$convertPath = null;
foreach ($imPaths as $path) {
    if (file_exists($path)) {
        $convertPath = $path;
        break;
    }
}

return [
    // Rutas detectadas automáticamente (null = no encontrado, usar defaults del sistema)
    'pdftotext_path' => $pdftotextPath,
    'python_path' => $pythonPath,
    'poppler_path' => $popplerPath,
    'convert_path' => $convertPath, // Nueva ruta detectada para ImageMagick
    
    // Rutas relativas para scripts (funcionan en cualquier PC)
    'paddle_script_path' => __DIR__ . '/../scripts/ocr_paddle.py',
    'ocr_ai_script_path' => __DIR__ . '/../scripts/ocr_ai_parser.py',
    
    // Configuración de OCR
    'enable_paddle_ocr' => false,   // Deshabilitado por defecto (requiere Python + PaddleOCR)
    'enable_ai_parser' => false,    // Deshabilitado por defecto (requiere Ollama)
    
    // Configuración de AI (si se habilita)
    'ollama_endpoint' => 'http://localhost:11434',
    'ollama_model' => 'llama3.2:3b',
    'ai_mode' => 'never',  // 'auto', 'always', 'never'
    
    // Configuración de fallback
    'ai_skip_required_keys' => ['rut', 'nombre'],
    'ai_skip_min_fields' => 2,
];
